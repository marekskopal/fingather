<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;

readonly class StrategyRebalancingDto
{
	/** @param list<StrategyRebalancingItemDto> $items */
	public function __construct(
		public int $id,
		public string $name,
		public Decimal $portfolioValue,
		public Decimal $cashToInvest,
		public array $items,
	) {
	}
}
