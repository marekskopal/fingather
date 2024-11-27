<?php

declare(strict_types=1);

namespace FinGather\Service\Warmup\Dto;

use FinGather\Dto\ArrayFactoryInterface;

/**
 * @implements ArrayFactoryInterface<array{
 *     userId: int,
 * }>
 */
final readonly class UserWarmupDto implements ArrayFactoryInterface
{
	public function __construct(public int $userId)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(userId: $data['userId']);
	}
}
