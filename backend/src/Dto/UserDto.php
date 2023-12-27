<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;

final readonly class UserDto
{
	public function __construct(
		public int $id,
		public string $email,
		public string $name,
		public int $defaultCurrencyId,
		public UserRoleEnum $role
	)
	{
	}

	public static function fromEntity(User $entity): self
	{
		return new self(
			id: $entity->getId(),
			email: $entity->getEmail(),
			name: $entity->getName(),
			defaultCurrencyId: $entity->getDefaultCurrency()->getId(),
			role: UserRoleEnum::from($entity->getRole()),
		);
	}
}
