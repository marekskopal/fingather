<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;

final readonly class CostBasisComparisonDto
{
	/** @param list<CostBasisComparisonRowDto> $rows */
	public function __construct(
		public int $year,
		public CostBasisMethodEnum $configuredMethod,
		public CostBasisMethodEnum $optimalMethod,
		public ?Decimal $estimatedTaxRate,
		public array $rows,
	) {
	}
}
