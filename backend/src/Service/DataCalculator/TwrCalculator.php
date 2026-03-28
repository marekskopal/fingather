<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\PortfolioCashFlowDto;
use FinGather\Utils\CalculatorUtils;

final class TwrCalculator implements TwrCalculatorInterface
{
	/**
	 * @param list<PortfolioCashFlowDto> $cashFlows
	 * @param callable(DateTimeImmutable): Decimal $portfolioValueFetcher
	 */
	public function calculate(
		array $cashFlows,
		callable $portfolioValueFetcher,
		Decimal $currentValue,
		DateTimeImmutable $currentDate,
	): float {
		if ($cashFlows === []) {
			return 0.0;
		}

		// Aggregate net cash flows per calendar day
		/** @var array<string, Decimal> $netByDate */
		$netByDate = [];
		/** @var array<string, DateTimeImmutable> $dateByKey */
		$dateByKey = [];

		foreach ($cashFlows as $cf) {
			$key = $cf->date->format('Y-m-d');
			if (!isset($netByDate[$key])) {
				$netByDate[$key] = new Decimal(0);
				$dateByKey[$key] = $cf->date;
			}

			$netByDate[$key] = $netByDate[$key]->add($cf->netCashFlow);
		}

		$twrProduct = 1.0;
		$prevValue = null;
		$lastKey = null;

		foreach ($netByDate as $key => $netCf) {
			/** @var Decimal $portfolioValueAtDate */
			$portfolioValueAtDate = $portfolioValueFetcher($dateByKey[$key]);

			if ($prevValue === null) {
				// First cash flow — set the baseline; no sub-period return to compute yet.
				$prevValue = $portfolioValueAtDate;
				$lastKey = $key;
				continue;
			}

			// V_end_before_CF = portfolio value on this date minus the net cash flow that day.
			$endValueBeforeCf = $portfolioValueAtDate->sub($netCf);

			if (!$prevValue->isZero()) {
				$subPeriodReturn = $endValueBeforeCf->div($prevValue)->toFloat() - 1.0;
				$twrProduct *= (1.0 + $subPeriodReturn);
			}

			$prevValue = $portfolioValueAtDate;
			$lastKey = $key;
		}

		// Final sub-period: from last cash-flow date to the calculation date (when different).
		// $prevValue and $lastKey are always set here (loop ran at least once — ensured by the
		// empty-cashFlows guard at the top of the method).
		$currentKey = $currentDate->format('Y-m-d');
		if ($lastKey !== $currentKey && !$prevValue->isZero()) {
			$subPeriodReturn = $currentValue->div($prevValue)->toFloat() - 1.0;
			$twrProduct *= (1.0 + $subPeriodReturn);
		}

		return CalculatorUtils::roundPercentage(($twrProduct - 1.0) * 100.0);
	}
}
