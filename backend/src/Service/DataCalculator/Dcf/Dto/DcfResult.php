<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dcf\Dto;

use Decimal\Decimal;

final readonly class DcfResult
{
	/**
	 * @param list<int> $projectedRevenues
	 * @param list<int> $projectedFcfes
	 */
	public function __construct(
		public Decimal $intrinsicValuePerShare,
		public Decimal $equityValue,
		public float $appliedGrowthRate,
		public float $appliedFcfMargin,
		public int $latestRevenue,
		public array $projectedRevenues,
		public array $projectedFcfes,
		public int $terminalFcfe,
		public Decimal $terminalValue,
		public Decimal $discountedTerminalValue,
		public DcfAssumptions $assumptions,
		public ?Decimal $currentPrice,
		public ?float $valuationDiffPercent,
		public ?DcfValuationStatusEnum $valuationStatus,
	) {
	}
}
