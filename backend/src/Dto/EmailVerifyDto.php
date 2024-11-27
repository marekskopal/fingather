<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\EmailVerify;
use FinGather\Model\Entity\Enum\UserRoleEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     user: array{
 *         id: int,
 *         email: string,
 *         name: string,
 *         defaultCurrencyId: int,
 *         role: value-of<UserRoleEnum>,
 *     },
 *     token: string,
 * }>
 */
final readonly class EmailVerifyDto implements ArrayFactoryInterface
{
	public function __construct(public UserDto $user, public string $token)
	{
	}

	public static function fromEntity(EmailVerify $emailVerify): self
	{
		return new self(
			user: UserDto::fromEntity($emailVerify->getUser()),
			token: $emailVerify->getToken(),
		);
	}

	public static function fromArray(array $data): static
	{
		return new self(
			user: UserDto::fromArray($data['user']),
			token: $data['token'],
		);
	}
}
