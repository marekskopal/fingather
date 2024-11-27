<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     type: value-of<ApiKeyTypeEnum>,
 *     apiKey: string
 * }>
 */
final readonly class ApiKeyCreateDto implements ArrayFactoryInterface
{
	public function __construct(public ApiKeyTypeEnum $type, public string $apiKey)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(
			type: ApiKeyTypeEnum::from($data['type']),
			apiKey: $data['apiKey'],
		);
	}
}
