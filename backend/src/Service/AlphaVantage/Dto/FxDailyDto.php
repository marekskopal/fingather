<?php

declare(strict_types=1);

namespace FinGather\Service\AlphaVantage\Dto;

use Decimal\Decimal;
use Safe\DateTimeImmutable;

readonly class FxDailyDto
{
	public function __construct(
		public DateTimeImmutable $date,
		public Decimal $open,
		public Decimal $high,
		public Decimal $low,
		public Decimal $close,
	) {
	}
}
