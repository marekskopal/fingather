<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Group;

readonly class GroupDto
{
	public function __construct(public int $id, public int $userId, public string $name,)
	{
	}

	public static function fromEntity(Group $entity): self
	{
		return new self(
			id: $entity->getId(),
			userId: $entity->getUser()->getId(),
			name: $entity->getName(),
		);
	}
}
