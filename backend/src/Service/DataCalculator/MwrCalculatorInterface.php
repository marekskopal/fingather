<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\PortfolioCashFlowDto;

interface MwrCalculatorInterface
{
	/**
	 * Calculate Money-Weighted Return (MWR / XIRR) as an annualised percentage.
	 *
	 * Uses Newton-Raphson iteration to solve for the rate r that satisfies:
	 *   Σ CF_i / (1+r)^t_i + V_end / (1+r)^t_end = 0
	 * where t_i is elapsed years from the first cash flow to each event and
	 * CF_i is expressed from the investor's perspective (Buy = negative, Sell = positive).
	 *
	 * @param list<PortfolioCashFlowDto> $cashFlows Portfolio-perspective (Buy = positive, Sell = negative).
	 */
	public function calculate(array $cashFlows, Decimal $endingValue, DateTimeImmutable $endDate,): float;
}
