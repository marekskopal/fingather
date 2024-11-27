<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     apiKey: string
 * }>
 */
final readonly class ApiKeyUpdateDto implements ArrayFactoryInterface
{
	public function __construct(public string $apiKey)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(apiKey: $data['apiKey']);
	}
}
