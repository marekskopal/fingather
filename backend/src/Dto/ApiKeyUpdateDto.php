<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     apiKey: string,
 *     userKey: string|null,
 * }>
 */
final readonly class ApiKeyUpdateDto implements ArrayFactoryInterface
{
	public function __construct(public string $apiKey, public ?string $userKey = null)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(apiKey: $data['apiKey'], userKey: $data['userKey'] ?? null);
	}
}
