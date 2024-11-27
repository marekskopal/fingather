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
			userId: $entity->getUser()->id,
			name: $entity->getName(),
			color: $entity->getColor(),
			assetIds: array_map(fn (Asset $asset): int => $asset->id, $entity->getAssets()),
		);
	}
}
