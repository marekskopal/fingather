<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Group;

readonly class GroupDto
{
	/** @param list<int> $assetIds */
	public function __construct(public int $id, public int $userId, public string $name, public string $color, public array $assetIds)
	{
	}

	public static function fromEntity(Group $entity): self
	{
		return new self(
			id: $entity->id,
			userId: $entity->user->id,
			name: $entity->name,
			color: $entity->color,
			assetIds: array_map(fn (Asset $asset): int => $asset->id, iterator_to_array($entity->assets, false)),
		);
	}
}
