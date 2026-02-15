<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class TaxReportRealizedGainsDto
{
	/** @param list<TaxReportRealizedGainTransactionDto> $transactions */
	public function __construct(
		public Decimal $totalSalesProceeds,
		public Decimal $totalCostBasis,
		public Decimal $totalGains,
		public Decimal $totalLosses,
		public Decimal $totalFees,
		public Decimal $netRealizedGainLoss,
		public array $transactions,
	) {
	}
}
