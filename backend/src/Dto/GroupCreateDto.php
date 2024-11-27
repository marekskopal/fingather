<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     name: string,
 *     color: string,
 *     asset_ids: list<int>,
 * }>
 */
final readonly class GroupCreateDto implements ArrayFactoryInterface
{
	/** @param list<int> $assetIds */
	public function __construct(public string $name, public string $color, public array $assetIds)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(name: $data['name'], color: $data['color'], assetIds: $data['asset_ids']);
	}
}
