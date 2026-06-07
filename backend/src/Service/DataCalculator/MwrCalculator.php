<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\PortfolioCashFlowDto;
use FinGather\Utils\CalculatorUtils;

final class MwrCalculator implements MwrCalculatorInterface
{
	private const int MaxIterations = 1000;
	private const float Tolerance = 1e-10;
	private const float InitialGuess = 0.1;

	/** @param list<PortfolioCashFlowDto> $cashFlows Portfolio-perspective (Buy = positive, Sell = negative). */
	public function calculate(array $cashFlows, Decimal $endingValue, DateTimeImmutable $endDate,): float
	{
		if ($cashFlows === []) {
			return 0.0;
		}

		// Build investor-perspective cash flow list: Buy = negative, Sell = positive.
		// t_i = elapsed years from first cash flow date.
		$firstDate = $cashFlows[0]->date;

		/** @var list<float> $eventCfs */
		$eventCfs = [];
		/** @var list<float> $eventTs */
		$eventTs = [];

		foreach ($cashFlows as $portfolioCf) {
			// Flip sign: portfolio perspective (Buy positive) → investor perspective (Buy negative)
			$eventCfs[] = -$portfolioCf->netCashFlow->toFloat();
			$eventTs[] = $portfolioCf->date->diff($firstDate)->days / 365.0;
		}

		// Add ending portfolio value as investor inflow at endDate.
		$eventCfs[] = $endingValue->toFloat();
		$eventTs[] = $endDate->diff($firstDate)->days / 365.0;

		// Newton-Raphson iteration.
		$r = self::InitialGuess;

		for ($i = 0; $i < self::MaxIterations; $i++) {
			$base = 1.0 + $r;

			// Guard against collapse to -1 or below.
			if ($base <= 0.0) {
				return -100.0;
			}

			$f = 0.0;
			$fPrime = 0.0;

			foreach ($eventCfs as $j => $cf) {
				$t = $eventTs[$j];
				$discount = $base ** (-$t);
				$f += $cf * $discount;
				$fPrime += -$t * $cf * $discount / $base;
			}

			if (abs($f) < self::Tolerance) {
				break;
			}

			if ($fPrime === 0.0) {
				break;
			}

			$r -= $f / $fPrime;
		}

		// r is already an annual rate; convert to percentage.
		return CalculatorUtils::roundPercentage($r * 100.0);
	}
}
