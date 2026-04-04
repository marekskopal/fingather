<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dcf;

use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfAssumptions;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfHistoryPointDto;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfInputs;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfResult;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfValuationStatusEnum;

final readonly class DcfCalculator implements DcfCalculatorInterface
{
	private const float FairValueThresholdPercent = 5.0;

	public function calculate(DcfInputs $inputs, DcfAssumptions $assumptions): DcfResult
	{
		if ($inputs->sharesOutstanding <= 0) {
			throw new DcfCalculationException('Shares outstanding must be positive.');
		}

		$latestRevenue = $this->resolveLatestRevenue($inputs);
		$fcfMargin = $this->resolveFcfMargin($inputs, $assumptions);
		$growthRate = $this->resolveGrowthRate($inputs, $assumptions);

		$projectedRevenues = [];
		$projectedFcfes = [];
		$pvSum = 0.0;
		$revenue = (float) $latestRevenue;

		for ($year = 1; $year <= $assumptions->projectionYears; $year++) {
			$revenue *= (1.0 + $growthRate);
			$fcfe = $revenue * $fcfMargin;
			$discountFactor = (1.0 + $assumptions->wacc) ** $year;

			$projectedRevenues[] = (int) $revenue;
			$projectedFcfes[] = (int) $fcfe;
			$pvSum += $fcfe / $discountFactor;
		}

		$lastFcfe = (float) end($projectedFcfes);
		$terminalFcfe = $lastFcfe * (1.0 + $assumptions->terminalGrowthRate);
		$terminalValue = $terminalFcfe / ($assumptions->wacc - $assumptions->terminalGrowthRate);
		$discountedTerminal = $terminalValue / ((1.0 + $assumptions->wacc) ** $assumptions->projectionYears);

		$equityValue = $pvSum + $discountedTerminal;
		$intrinsicPerShare = $equityValue / $inputs->sharesOutstanding;

		[$valuationDiffPercent, $valuationStatus] = $this->classifyValuation($inputs->currentPrice, $intrinsicPerShare);

		return new DcfResult(
			intrinsicValuePerShare: $this->toDecimal($intrinsicPerShare),
			equityValue: $this->toDecimal($equityValue),
			appliedGrowthRate: $growthRate,
			appliedFcfMargin: $fcfMargin,
			latestRevenue: $latestRevenue,
			projectedRevenues: $projectedRevenues,
			projectedFcfes: $projectedFcfes,
			terminalFcfe: (int) $terminalFcfe,
			terminalValue: $this->toDecimal($terminalValue),
			discountedTerminalValue: $this->toDecimal($discountedTerminal),
			assumptions: $assumptions,
			currentPrice: $inputs->currentPrice,
			valuationDiffPercent: $valuationDiffPercent,
			valuationStatus: $valuationStatus,
		);
	}

	/**
	 * AlphaSpread-style: diff as a fraction of current market price.
	 * Positive → overvalued by that %; negative → undervalued.
	 *
	 * @return array{0: ?float, 1: ?DcfValuationStatusEnum}
	 */
	private function classifyValuation(?Decimal $currentPrice, float $intrinsicPerShare): array
	{
		if ($currentPrice === null) {
			return [null, null];
		}

		$price = (float) (string) $currentPrice;
		if ($price === 0.0) {
			return [null, null];
		}

		$diffPercent = (($price - $intrinsicPerShare) / $price) * 100.0;

		if ($diffPercent > self::FairValueThresholdPercent) {
			$status = DcfValuationStatusEnum::Overvalued;
		} elseif ($diffPercent < -self::FairValueThresholdPercent) {
			$status = DcfValuationStatusEnum::Undervalued;
		} else {
			$status = DcfValuationStatusEnum::FairlyValued;
		}

		return [$diffPercent, $status];
	}

	private function resolveLatestRevenue(DcfInputs $inputs): int
	{
		foreach ($inputs->history as $point) {
			if ($point->revenue !== null && $point->revenue > 0) {
				return $point->revenue;
			}
		}

		if ($inputs->latestRevenue !== null && $inputs->latestRevenue > 0) {
			return $inputs->latestRevenue;
		}

		throw new DcfCalculationException('Latest revenue is unavailable.');
	}

	private function resolveFcfMargin(DcfInputs $inputs, DcfAssumptions $assumptions): float
	{
		if ($assumptions->fcfMarginOverride !== null) {
			return $assumptions->fcfMarginOverride;
		}

		$margins = [];
		foreach ($inputs->history as $point) {
			if ($point->revenue === null || $point->revenue <= 0 || $point->freeCashFlow === null) {
				continue;
			}

			$margins[] = $point->freeCashFlow / $point->revenue;
		}

		if (count($margins) > 0) {
			return array_sum($margins) / count($margins);
		}

		if ($inputs->latestFcfe !== null && $inputs->latestRevenue !== null && $inputs->latestRevenue > 0) {
			return $inputs->latestFcfe / $inputs->latestRevenue;
		}

		throw new DcfCalculationException('FCF margin cannot be derived.');
	}

	private function resolveGrowthRate(DcfInputs $inputs, DcfAssumptions $assumptions): float
	{
		if ($assumptions->growthRateOverride !== null) {
			return $this->clampGrowth($assumptions->growthRateOverride, $assumptions);
		}

		$signals = [];
		if ($inputs->quarterlyRevenueGrowth !== null) {
			$signals[] = $inputs->quarterlyRevenueGrowth;
		}

		$cagr = $this->computeRevenueCagr($inputs->history);
		if ($cagr !== null) {
			$signals[] = $cagr;
		}

		if (count($signals) === 0) {
			throw new DcfCalculationException('Growth rate cannot be derived.');
		}

		return $this->clampGrowth(array_sum($signals) / count($signals), $assumptions);
	}

	/** @param list<DcfHistoryPointDto> $history */
	private function computeRevenueCagr(array $history): ?float
	{
		$revenues = [];
		foreach ($history as $point) {
			if ($point->revenue !== null && $point->revenue > 0) {
				$revenues[] = $point->revenue;
			}
		}

		$count = count($revenues);
		if ($count < 2) {
			return null;
		}

		$latest = $revenues[0];
		$oldest = $revenues[$count - 1];
		$years = $count - 1;

		return ($latest / $oldest) ** (1.0 / $years) - 1.0;
	}

	private function clampGrowth(float $rate, DcfAssumptions $assumptions): float
	{
		return max($assumptions->minGrowthRate, min($assumptions->maxGrowthRate, $rate));
	}

	private function toDecimal(float $value): Decimal
	{
		return new Decimal(number_format($value, 8, '.', ''));
	}
}
