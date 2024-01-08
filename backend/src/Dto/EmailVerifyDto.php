<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\User;
use SensitiveParameter;

final readonly class EmailVerifyDto
{
	public function __construct(#[SensitiveParameter] public string $email, public string $verifyToken,)
	{
	}

	public static function fromEntity(User $user): self
	{
		return new self(
			email: $user->getEmail(),
			verifyToken: $user->getEmail(),
		);
	}
}
