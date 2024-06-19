<?php

declare(strict_types=1);

namespace FinGather\Dto;

use SensitiveParameter;

final readonly class RefreshTokenDto
{
	public function __construct(#[SensitiveParameter] public string $refreshToken,)
	{
	}

	/**
	 * @param array{
	 *     refreshToken: string,
	 * } $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(refreshToken: $data['refreshToken']);
	}
}
