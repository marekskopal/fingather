<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     assetId: int|null,
 *     groupId: int|null,
 *     isOthers: bool,
 *     percentage: float,
 * }>
 */
final readonly class StrategyItemCreateDto implements ArrayFactoryInterface
{
	public function __construct(
		public ?int $assetId,
		public ?int $groupId,
		public bool $isOthers,
		public float $percentage,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			assetId: $data['assetId'] ?? null,
			groupId: $data['groupId'] ?? null,
			isOthers: $data['isOthers'],
			percentage: $data['percentage'],
		);
	}
}
