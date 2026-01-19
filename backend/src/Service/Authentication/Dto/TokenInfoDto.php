<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication\Dto;

final readonly class TokenInfoDto
{
	public function __construct(
		public string $sub,
		public string $email,
		public string $name,
		public string $aud,
		public bool $emailVerified,
	) {
	}

	/** @param array{sub: string, email: string, name: string, aud: string, email_verified: string} $data */
	public static function fromArray(array $data): self
	{
		return new self(
			sub: $data['sub'],
			email: $data['email'],
			name: $data['name'],
			aud: $data['aud'],
			emailVerified: $data['email_verified'] === 'true',
		);
	}
}
