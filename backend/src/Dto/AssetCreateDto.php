<?php

declare(strict_types=1);

namespace FinGather\Dto;

/** @implements ArrayFactoryInterface<array{tickerId: int}> */
final readonly class AssetCreateDto implements ArrayFactoryInterface
{
	public function __construct(public int $tickerId)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(tickerId: $data['tickerId']);
	}
}