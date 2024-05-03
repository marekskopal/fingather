<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;

final readonly class UserDto
{
	public function __construct(public int $id, public string $email, public string $name, public UserRoleEnum $role,)
	{
	}

	public static function fromEntity(User $entity): self
	{
		return new self(
			id: $entity->getId(),
			email: $entity->getEmail(),
			name: $entity->getName(),
			role: $entity->getRole(),
		);
	}

	/**
	 * @param array{
	 *     id: int,
	 *     email: string,
	 *     name: string,
	 *     role: value-of<UserRoleEnum>,
	 * } $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			id: $data['id'],
			email: $data['email'],
			name: $data['name'],
			role: UserRoleEnum::from($data['role']),
		);
	}
}
