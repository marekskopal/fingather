<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class TaxReportRealizedGainTransactionDto
{
	public function __construct(
		public string $tickerTicker,
		public string $tickerName,
		public string $buyDate,
		public string $sellDate,
		public int $holdingPeriodDays,
		public Decimal $units,
		public Decimal $buyPrice,
		public Decimal $sellPrice,
		public Decimal $costBasis,
		public Decimal $salesProceeds,
		public Decimal $fee,
		public Decimal $gainLoss,
	) {
	}
}
