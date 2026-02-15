<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class TaxReportDto
{
	public function __construct(
		public int $year,
		public TaxReportRealizedGainsDto $realizedGains,
		public TaxReportUnrealizedDto $unrealizedPositions,
		public TaxReportDividendsDto $dividends,
		public Decimal $totalFees,
		public Decimal $totalTaxes,
	) {
	}
}
