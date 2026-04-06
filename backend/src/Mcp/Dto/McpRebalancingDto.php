<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Dto\StrategyRebalancingDto;
use FinGather\Dto\StrategyRebalancingItemDto;
use FinGather\Model\Entity\Portfolio;

final readonly class McpRebalancingDto
{
	/** @param list<McpRebalancingItemDto> $items */
	public function __construct(
		public int $strategyId,
		public string $strategyName,
		public string $portfolioCurrency,
		public string $portfolioValue,
		public string $cashToInvest,
		public bool $allowSelling,
		public array $items,
	) {
	}

	public static function fromDto(StrategyRebalancingDto $rebalancing, Portfolio $portfolio, bool $allowSelling): self
	{
		$items = array_map(
			fn (StrategyRebalancingItemDto $item): McpRebalancingItemDto => McpRebalancingItemDto::fromDto($item),
			$rebalancing->items,
		);

		return new self(
			strategyId: $rebalancing->id,
			strategyName: $rebalancing->name,
			portfolioCurrency: $portfolio->currency->code,
			portfolioValue: (string) $rebalancing->portfolioValue,
			cashToInvest: (string) $rebalancing->cashToInvest,
			allowSelling: $allowSelling,
			items: $items,
		);
	}
}
