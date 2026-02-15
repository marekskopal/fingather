<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class TaxReportUnrealizedPositionDto
{
	public function __construct(
		public string $tickerTicker,
		public string $tickerName,
		public string $firstBuyDate,
		public int $holdingPeriodDays,
		public Decimal $units,
		public Decimal $buyPrice,
		public Decimal $costBasis,
		public Decimal $marketValue,
		public Decimal $gainLoss,
	) {
	}
}
