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
		public UserRoleEnum $role,
	) {
	}

	public static function fromEntity(User $entity): self
	{
		return new self(
			id: $entity->getId(),
			email: $entity->getEmail(),
			name: $entity->getName(),
			defaultCurrencyId: $entity->getDefaultCurrency()->getId(),
			role: $entity->getRole(),
		);
	}

	/**
	 * @param array{
	 *     id: int,
	 *     email: string,
	 *     name: string,
	 *     defaultCurrencyId: int,
	 *     role: value-of<UserRoleEnum>,
	 * } $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			id: $data['id'],
			email: $data['email'],
			name: $data['name'],
			defaultCurrencyId: $data['defaultCurrencyId'],
			role: UserRoleEnum::from($data['role']),
		);
	}
}
