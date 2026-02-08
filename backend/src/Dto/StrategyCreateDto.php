<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     name: string,
 *     isDefault: bool,
 *     items: list<array{
 *         assetId: int|null,
 *         groupId: int|null,
 *         isOthers: bool,
 *         percentage: float,
 *     }>,
 * }>
 */
final readonly class StrategyCreateDto implements ArrayFactoryInterface
{
	/** @param list<StrategyItemCreateDto> $items */
	public function __construct(
		public string $name,
		public bool $isDefault,
		public array $items,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			name: $data['name'],
			isDefault: $data['isDefault'],
			items: array_map(
				fn (array $item): StrategyItemCreateDto => StrategyItemCreateDto::fromArray($item),
				$data['items'],
			),
		);
	}
}
