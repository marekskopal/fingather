<?php

declare(strict_types=1);

namespace FinGather\Dto;

use SensitiveParameter;

/**
 * @implements ArrayFactoryInterface<array{
 *      refreshToken: string,
 * }>
 */
final readonly class RefreshTokenDto implements ArrayFactoryInterface
{
	public function __construct(#[SensitiveParameter] public string $refreshToken,)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(refreshToken: $data['refreshToken']);
	}
}
