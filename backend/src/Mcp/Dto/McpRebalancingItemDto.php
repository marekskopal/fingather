<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Dto\StrategyRebalancingItemDto;

final readonly class McpRebalancingItemDto
{
	public function __construct(
		public string $name,
		public ?int $assetId,
		public ?int $groupId,
		public float $targetPercentage,
		public float $actualPercentage,
		public float $differencePercentage,
		public string $currentValue,
		public string $targetValue,
		public string $suggestedTradeValue,
		public ?string $suggestedTradeUnits,
		public ?string $currentPrice,
	) {
	}

	public static function fromDto(StrategyRebalancingItemDto $item): self
	{
		return new self(
			name: $item->name,
			assetId: $item->assetId,
			groupId: $item->groupId,
			targetPercentage: $item->targetPercentage,
			actualPercentage: $item->actualPercentage,
			differencePercentage: $item->differencePercentage,
			currentValue: (string) $item->currentValue,
			targetValue: (string) $item->targetValue,
			suggestedTradeValue: (string) $item->suggestedTradeValue,
			suggestedTradeUnits: $item->suggestedTradeUnits !== null ? (string) $item->suggestedTradeUnits : null,
			currentPrice: $item->currentPrice !== null ? (string) $item->currentPrice : null,
		);
	}
}
