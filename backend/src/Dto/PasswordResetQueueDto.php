<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\UserPlanEnum;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\PasswordReset;

/**
 * @implements ArrayFactoryInterface<array{
 *     user: array{
 *         id: int,
 *         email: string,
 *         name: string,
 *         defaultCurrencyId: int,
 *         role: value-of<UserRoleEnum>,
 *         plan: value-of<UserPlanEnum>,
 *         planExpires: string|null,
 *     },
 *     token: string,
 * }>
 */
final readonly class PasswordResetQueueDto implements ArrayFactoryInterface
{
	public function __construct(public UserDto $user, public string $token)
	{
	}

	public static function fromEntity(PasswordReset $passwordReset): self
	{
		return new self(
			user: UserDto::fromEntity($passwordReset->user),
			token: $passwordReset->token,
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
