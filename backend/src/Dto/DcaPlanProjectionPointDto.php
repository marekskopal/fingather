<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;

final readonly class DcaPlanProjectionPointDto
{
	public function __construct(
		public int $id,
		public string $date,
		public Decimal $investedCapital,
		public Decimal $projectedValue,
		public ?Decimal $p10 = null,
		public ?Decimal $p50 = null,
		public ?Decimal $p90 = null,
	) {
	}
}
