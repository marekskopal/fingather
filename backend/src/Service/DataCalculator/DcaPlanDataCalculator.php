<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateInterval;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\DcaPlanProjectionDto;
use FinGather\Dto\DcaPlanProjectionPointDto;
use FinGather\Dto\Enum\AssetOrderEnum;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\ReturnRateDto;
use FinGather\Service\DataCalculator\Dto\TickerWeightDto;
use FinGather\Service\Provider\AssetWithPropertiesProvider;
use FinGather\Service\Provider\TickerDataProvider;

final readonly class DcaPlanDataCalculator
{
	private const int HistoryYears = 10;
	private const int MinDays = 365;
	private const int MonthsPerYear = 12;
	private const float AvgSecondsPerMonth = 30.4375 * 24 * 3600;
	private const int DecimalPrecision = 8;

	public function __construct(
		private TickerDataProvider $tickerDataProvider,
		private AssetWithPropertiesProvider $assetWithPropertiesProvider,
	) {
	}

	public function calculateReturnRate(DcaPlan $dcaPlan): ReturnRateDto
	{
		$tickerWeights = $this->getTickerWeights($dcaPlan);

		if (count($tickerWeights) === 0) {
			return new ReturnRateDto(annual: 0.0, monthly: 0.0);
		}

		$totalWeight = 0.0;
		foreach ($tickerWeights as $tickerWeight) {
			$totalWeight += $tickerWeight->weight;
		}

		if ($totalWeight === 0.0) {
			return new ReturnRateDto(annual: 0.0, monthly: 0.0);
		}

		$weightedReturn = 0.0;
		foreach ($tickerWeights as $tickerWeight) {
			$weightedReturn += $this->calculateTickerReturnRate($tickerWeight->tickerId) * $tickerWeight->weight / $totalWeight;
		}

		$annualRate = round($weightedReturn, 2);
		$monthlyRate = ((1 + $annualRate / 100) ** (1 / self::MonthsPerYear) - 1) * 100;

		return new ReturnRateDto(annual: $annualRate, monthly: $monthlyRate);
	}

	public function getProjection(DcaPlan $dcaPlan, int $horizonYears = 10, bool $withCurrentValue = true): DcaPlanProjectionDto
	{
		$returnRate = $this->calculateReturnRate($dcaPlan);
		$monthlyRate = $returnRate->monthly / 100;
		$amount = $dcaPlan->amount->toFloat();
		$startValue = $withCurrentValue ? $this->getCurrentValue($dcaPlan) : 0.0;

		$maxMonths = $horizonYears * self::MonthsPerYear;
		if ($dcaPlan->endDate !== null) {
			$startTimestamp = $dcaPlan->startDate->getTimestamp();
			$endTimestamp = $dcaPlan->endDate->getTimestamp();
			$diffMonths = (int) round(($endTimestamp - $startTimestamp) / self::AvgSecondsPerMonth);
			$maxMonths = min($maxMonths, max(1, $diffMonths));
		}

		$dataPoints = [];
		for ($n = 1; $n <= $maxMonths; $n++) {
			$investedCapital = $startValue + $amount * $n;

			$projectedValue = $monthlyRate !== 0.0 ? $startValue * (1 + $monthlyRate) ** $n
					+ $amount * ((1 + $monthlyRate) ** $n - 1) / $monthlyRate : $investedCapital;

			$date = $dcaPlan->startDate->modify('+' . ($n - 1) . ' months');
			$dataPoints[] = new DcaPlanProjectionPointDto(
				id: $n,
				date: $date->format('Y-m'),
				investedCapital: new Decimal((string) round($investedCapital, self::DecimalPrecision)),
				projectedValue: new Decimal((string) round($projectedValue, self::DecimalPrecision)),
			);
		}

		return new DcaPlanProjectionDto(dataPoints: $dataPoints);
	}

	private function getCurrentValue(DcaPlan $dcaPlan): float
	{
		$openAssets = $this->assetWithPropertiesProvider->getAssetsWithAssetData(
			$dcaPlan->user,
			$dcaPlan->portfolio,
			new DateTimeImmutable('today'),
			AssetOrderEnum::TickerName,
		)->openAssets;

		return match ($dcaPlan->targetType) {
			DcaPlanTargetTypeEnum::Portfolio, DcaPlanTargetTypeEnum::Strategy =>
				array_sum(array_map(fn (AssetWithPropertiesDto $a) => $a->value->toFloat(), $openAssets)),
			DcaPlanTargetTypeEnum::Asset => $dcaPlan->asset !== null
				? array_sum(array_map(
					fn (AssetWithPropertiesDto $a) => $a->value->toFloat(),
					array_filter($openAssets, fn (AssetWithPropertiesDto $a) => $a->tickerId === $dcaPlan->asset->ticker->id),
				))
				: 0.0,
			DcaPlanTargetTypeEnum::Group => $dcaPlan->group !== null
				? array_sum(array_map(
					fn (AssetWithPropertiesDto $a) => $a->value->toFloat(),
					array_filter($openAssets, fn (AssetWithPropertiesDto $a) => $a->groupId === $dcaPlan->group->id),
				))
				: 0.0,
		};
	}

	/** @return list<TickerWeightDto> */
	private function getTickerWeights(DcaPlan $dcaPlan): array
	{
		return match ($dcaPlan->targetType) {
			DcaPlanTargetTypeEnum::Asset => $dcaPlan->asset !== null
				? [new TickerWeightDto($dcaPlan->asset->ticker->id, 1.0)]
				: [],
			DcaPlanTargetTypeEnum::Group => $dcaPlan->group !== null
				? $this->getGroupTickerWeights($dcaPlan->group)
				: [],
			DcaPlanTargetTypeEnum::Strategy => $dcaPlan->strategy !== null
				? $this->getStrategyTickerWeights($dcaPlan->strategy)
				: [],
			DcaPlanTargetTypeEnum::Portfolio => $this->getPortfolioTickerWeights($dcaPlan->portfolio, $dcaPlan->user),
		};
	}

	/**
	 * All assets in the group carry equal weight.
	 *
	 * @return list<TickerWeightDto>
	 */
	private function getGroupTickerWeights(Group $group): array
	{
		$tickerWeights = [];
		foreach ($group->assets as $asset) {
			$tickerWeights[$asset->ticker->id] = new TickerWeightDto($asset->ticker->id, 1.0);
		}

		return array_values($tickerWeights);
	}

	/**
	 * Each strategy item contributes its defined percentage as the weight.
	 * When a strategy item targets a Group, its percentage is split equally among the group's assets.
	 *
	 * @return list<TickerWeightDto>
	 */
	private function getStrategyTickerWeights(Strategy $strategy): array
	{
		/** @var array<int, float> $tickerWeights */
		$tickerWeights = [];
		foreach ($strategy->strategyItems as $strategyItem) {
			$weight = $strategyItem->percentage->toFloat();

			if ($strategyItem->asset !== null) {
				$tickerId = $strategyItem->asset->ticker->id;
				$tickerWeights[$tickerId] = ($tickerWeights[$tickerId] ?? 0.0) + $weight;
			} elseif ($strategyItem->group !== null) {
				$groupAssets = iterator_to_array($strategyItem->group->assets, false);
				$groupAssetCount = count($groupAssets);
				if ($groupAssetCount === 0) {
					continue;
				}

				$assetWeight = $weight / $groupAssetCount;
				foreach ($groupAssets as $asset) {
					$tickerId = $asset->ticker->id;
					$tickerWeights[$tickerId] = ($tickerWeights[$tickerId] ?? 0.0) + $assetWeight;
				}
			}
		}

		return array_map(
			fn (int $tickerId, float $weight) => new TickerWeightDto($tickerId, $weight),
			array_keys($tickerWeights),
			$tickerWeights,
		);
	}

	/**
	 * Assets are weighted by their current percentage of the portfolio value.
	 *
	 * @return list<TickerWeightDto>
	 */
	private function getPortfolioTickerWeights(Portfolio $portfolio, User $user): array
	{
		$assetsWithProperties = $this->assetWithPropertiesProvider->getAssetsWithAssetData(
			$user,
			$portfolio,
			new DateTimeImmutable('today'),
			AssetOrderEnum::TickerName,
		);

		$tickerWeights = [];
		foreach ($assetsWithProperties->openAssets as $assetDto) {
			$tickerWeights[$assetDto->tickerId] = new TickerWeightDto($assetDto->tickerId, $assetDto->percentage);
		}

		return array_values($tickerWeights);
	}

	private function calculateTickerReturnRate(int $tickerId): float
	{
		$toDate = new DateTimeImmutable('today');
		$fromDate = $toDate->sub(new DateInterval('P' . self::HistoryYears . 'Y'));

		$firstData = $this->tickerDataProvider->getFirstTickerData($tickerId, $fromDate);
		$lastData = $this->tickerDataProvider->getLastTickerData($tickerId, $toDate);

		if ($firstData === null || $lastData === null || $firstData->date >= $lastData->date || $firstData->close->isZero()) {
			return 0.0;
		}

		$days = (int) $lastData->date->diff($firstData->date)->days;
		$years = max($days, self::MinDays) / 365.0;
		$cagr = (($lastData->close->toFloat() / $firstData->close->toFloat()) ** (1.0 / $years) - 1.0) * 100.0;

		return round($cagr, 2);
	}
}
