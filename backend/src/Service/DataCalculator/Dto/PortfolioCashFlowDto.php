<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use DateTimeImmutable;
use Decimal\Decimal;

/** Net cash flow into the portfolio on a given date (Buy = positive, Sell = negative). */
final readonly class PortfolioCashFlowDto
{
	public function __construct(public DateTimeImmutable $date, public Decimal $netCashFlow,)
	{
	}
}
