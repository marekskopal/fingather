<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class IndustryWithIndustryDataDto
{
	public function __construct(
		public int $id,
		public int $userId,
		public string $name,
		public float $percentage,
		public IndustryDataDto $industryData,
	) {
	}
}
