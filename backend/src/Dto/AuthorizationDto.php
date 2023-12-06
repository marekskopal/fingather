<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class AuthorizationDto
{
	public function __construct(
		public string $token,
		public int $tokenExpirationTime,
		public int $id,
	) {
	}
}
