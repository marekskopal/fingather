<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\StrategyItem;

final readonly class McpStrategyItemDto
{
	public function __construct(public string $name, public float $targetPercentage, public ?int $assetId, public ?int $groupId,)
	{
	}

	/** @api */
	public static function fromEntity(StrategyItem $item): self
	{
		return new self(
			name: $item->asset?->ticker->name ?? $item->group->name ?? 'Others',
			targetPercentage: $item->percentage->toFloat(),
			assetId: $item->asset?->id,
			groupId: $item->group?->id,
		);
	}
}
