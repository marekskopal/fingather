<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     type: value-of<ApiKeyTypeEnum>,
 *     apiKey: string,
 *     userKey: string|null,
 * }>
 */
final readonly class ApiKeyCreateDto implements ArrayFactoryInterface
{
	public function __construct(public ApiKeyTypeEnum $type, public string $apiKey, public ?string $userKey = null)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(
			type: ApiKeyTypeEnum::from($data['type']),
			apiKey: $data['apiKey'],
			userKey: $data['userKey'] ?? null,
		);
	}
}
