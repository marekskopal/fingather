<?php

declare(strict_types=1);

namespace FinGather\Service\Warmup\Dto;

final readonly class UserWarmupDto
{
	public function __construct(public int $userId)
	{
	}

	/**
	 * @param array{
	 *     userId: int
	 * } $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(userId: $data['userId']);
	}
}
