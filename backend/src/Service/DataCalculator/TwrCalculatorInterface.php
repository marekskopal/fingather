<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\PortfolioCashFlowDto;

interface TwrCalculatorInterface
{
	/**
	 * Calculate Time-Weighted Return (TWR) as a percentage.
	 *
	 * Splits the period into sub-periods at each cash flow date.
	 * For each sub-period the return is computed as (V_end_before_CF / V_start) - 1.
	 * The sub-period returns are then chained geometrically.
	 *
	 * @param list<PortfolioCashFlowDto> $cashFlows  Cash flows sorted ascending (Buy = positive, Sell = negative).
	 * @param callable(DateTimeImmutable): Decimal $portfolioValueFetcher Returns portfolio value for a given date.
	 */
	public function calculate(
		array $cashFlows,
		callable $portfolioValueFetcher,
		Decimal $currentValue,
		DateTimeImmutable $currentDate,
	): float;
}
