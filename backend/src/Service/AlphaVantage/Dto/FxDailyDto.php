<?php

declare(strict_types=1);

namespace FinGather\Service\AlphaVantage\Dto;

use Safe\DateTimeImmutable;

readonly class FxDailyDto
{
	public function __construct(
		public DateTimeImmutable $date,
		public float $open,
		public float $high,
		public float $low,
		public float $close,
	)
	{
	}
}
