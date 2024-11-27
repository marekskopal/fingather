<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\UserRoleEnum;
use SensitiveParameter;

/**
 * @implements ArrayFactoryInterface<array{
 *     name: string,
 *     password: string,
 *     defaultCurrencyId: int,
 *     role: value-of<UserRoleEnum>,
 * }>
 */
final readonly class UserUpdateDto implements ArrayFactoryInterface
{
	public function __construct(
		#[SensitiveParameter] public string $password,
		public string $name,
		public int $defaultCurrencyId,
		public UserRoleEnum $role,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			name: $data['name'],
			password: $data['password'],
			defaultCurrencyId: $data['defaultCurrencyId'],
			role: UserRoleEnum::from($data['role']),
		);
	}
}
