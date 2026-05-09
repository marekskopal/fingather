<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;

final readonly class CostBasisComparisonRowDto
{
	public function __construct(
		public CostBasisMethodEnum $method,
		public bool $allowedInJurisdiction,
		public Decimal $totalSalesProceeds,
		public Decimal $totalCostBasis,
		public Decimal $totalGains,
		public Decimal $totalLosses,
		public Decimal $netRealizedGainLoss,
		public ?Decimal $estimatedTax,
		public ?Decimal $deltaVsConfigured,
	) {
	}
}
