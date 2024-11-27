<?php

declare(strict_types=1);

namespace FinGather\Dto;

use SensitiveParameter;

/**
 * @implements ArrayFactoryInterface<array{
 *     email: string,
 * }>
 */
final readonly class EmailExistsDto implements ArrayFactoryInterface
{
	public function __construct(#[SensitiveParameter] public string $email)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(email: $data['email']);
	}
}
