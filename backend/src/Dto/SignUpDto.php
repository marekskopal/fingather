<?php

declare(strict_types=1);

namespace FinGather\Dto;

use SensitiveParameter;

final readonly class SignUpDto
{
	public function __construct(
		#[SensitiveParameter] public string $email,
		#[SensitiveParameter] public string $password,
		public string $name,
		public int $defaultCurrencyId,
	) {
	}

	/**
	 * @param array{
	 *     email: string,
	 *     name: string,
	 *     password: string,
	 *     defaultCurrencyId: int,
	 * } $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			email: $data['email'],
			name: $data['name'],
			password: $data['password'],
			defaultCurrencyId: $data['defaultCurrencyId'],
		);
	}
}
