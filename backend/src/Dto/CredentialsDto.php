<?php

declare(strict_types=1);

namespace FinGather\Dto;

use SensitiveParameter;

/**
 * @implements ArrayFactoryInterface<array{
 *     email: string,
 *     password: string
 * }>
 */
final readonly class CredentialsDto implements ArrayFactoryInterface
{
	public function __construct(#[SensitiveParameter] public string $email, #[SensitiveParameter] public string $password,)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(email: $data['email'], password: $data['password']);
	}
}
