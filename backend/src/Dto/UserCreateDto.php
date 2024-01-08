<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\UserRoleEnum;
use SensitiveParameter;

final readonly class UserCreateDto
{
	public function __construct(
		#[SensitiveParameter] public string $email,
		#[SensitiveParameter] public string $password,
		public string $name,
		public int $defaultCurrencyId,
		public UserRoleEnum $role,
	) {
	}

	/**
	 * @param array{
	 *     email: string,
	 *     name: string,
	 *     password: string,
	 *     defaultCurrencyId: int,
	 *     role: value-of<UserRoleEnum>,
	 * } $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			email: $data['email'],
			name: $data['name'],
			password: $data['password'],
			defaultCurrencyId: $data['defaultCurrencyId'],
			role: UserRoleEnum::from($data['role']),
		);
	}
}
