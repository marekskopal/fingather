<?php

declare(strict_types=1);

namespace FinGather\Dto;

abstract readonly class AbstractGroupWithGroupDataDto
{
	public function __construct(
		public int $id,
		public int $userId,
		public string $name,
		public float $percentage,
		public AbstractGroupDataDto $groupData,
	) {
	}
}
