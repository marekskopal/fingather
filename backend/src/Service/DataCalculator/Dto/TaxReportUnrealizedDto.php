<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class TaxReportUnrealizedDto
{
	/** @param list<TaxReportUnrealizedPositionDto> $positions */
	public function __construct(
		public Decimal $totalMarketValue,
		public Decimal $totalCostBasis,
		public Decimal $totalGainLoss,
		public array $positions,
	) {
	}
}
