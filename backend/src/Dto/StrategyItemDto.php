<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\StrategyItem;

final readonly class StrategyItemDto
{
	public function __construct(
		public int $id,
		public int $strategyId,
		public ?int $assetId,
		public ?int $groupId,
		public bool $isOthers,
		public float $percentage,
		public string $name,
	) {
	}

	public static function fromEntity(StrategyItem $entity): self
	{
		if ($entity->asset !== null) {
			$name = $entity->asset->ticker->name;
		} elseif ($entity->group !== null) {
			$name = $entity->group->name;
		} else {
			$name = 'Unknown';
		}

		return new self(
			id: $entity->id,
			strategyId: $entity->strategy->id,
			assetId: $entity->asset?->id,
			groupId: $entity->group?->id,
			isOthers: false,
			percentage: $entity->percentage->toFloat(),
			name: $name,
		);
	}
}
