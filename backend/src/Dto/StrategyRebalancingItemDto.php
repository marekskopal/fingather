<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;

readonly class StrategyRebalancingItemDto
{
	public function __construct(
		public string $name,
		public ?string $color,
		public ?int $assetId,
		public ?int $groupId,
		public bool $isOthers,
		public float $targetPercentage,
		public float $actualPercentage,
		public float $differencePercentage,
		public Decimal $currentValue,
		public Decimal $targetValue,
		public Decimal $suggestedTradeValue,
		public ?Decimal $suggestedTradeUnits,
		public ?Decimal $currentPrice,
	) {
	}
}
