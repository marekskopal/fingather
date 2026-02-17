<?php

declare(strict_types=1);

namespace FinGather\Dto;

readonly class StrategyComparisonItemDto
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
	) {
	}
}
