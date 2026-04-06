<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\StrategyItem;

final readonly class McpStrategyDto
{
	/** @param list<McpStrategyItemDto> $items */
	public function __construct(public int $strategyId, public string $name, public bool $isDefault, public array $items,)
	{
	}

	public static function fromEntity(Strategy $entity): self
	{
		$items = array_map(
			fn (StrategyItem $item): McpStrategyItemDto => McpStrategyItemDto::fromEntity($item),
			iterator_to_array($entity->strategyItems, false),
		);

		return new self(strategyId: $entity->id, name: $entity->name, isDefault: $entity->isDefault, items: $items);
	}
}
