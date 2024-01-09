<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\EmailVerify;

final readonly class EmailVerifyDto
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
}
