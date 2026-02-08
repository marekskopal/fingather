<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\StrategyItem;

readonly class StrategyDto
{
	/** @param list<StrategyItemDto> $items */
	public function __construct(
		public int $id,
		public int $userId,
		public int $portfolioId,
		public string $name,
		public bool $isDefault,
		public array $items,
	) {
	}

	public static function fromEntity(Strategy $entity): self
	{
		$items = array_map(
			fn (StrategyItem $item): StrategyItemDto => StrategyItemDto::fromEntity($item),
			iterator_to_array($entity->strategyItems, false),
		);

		$totalPercentage = 0.0;
		foreach ($items as $item) {
			$totalPercentage += $item->percentage;
		}

		$othersPercentage = round(100.0 - $totalPercentage, 2);
		if ($othersPercentage > 0.0) {
			$items[] = new StrategyItemDto(
				id: 0,
				strategyId: $entity->id,
				assetId: null,
				groupId: null,
				isOthers: true,
				percentage: $othersPercentage,
				name: 'Others',
			);
		}

		return new self(
			id: $entity->id,
			userId: $entity->user->id,
			portfolioId: $entity->portfolio->id,
			name: $entity->name,
			isDefault: $entity->isDefault,
			items: $items,
		);
	}
}
