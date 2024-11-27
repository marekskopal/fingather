<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     token: string,
 * }>
 */
final readonly class EmailVerifyCreateDto implements ArrayFactoryInterface
{
	public function __construct(public string $token)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(token: $data['token']);
	}
}
