<?php

declare(strict_types=1);

namespace FinGather\Service\AlphaVantage\Dto;

use Safe\DateTimeImmutable;

readonly class TimeSerieDailyDto
{
	public function __construct(
		public DateTimeImmutable $date,
		public float $open,
		public float $high,
		public float $low,
		public float $close,
		public float $adjustedClose,
		public int $volume,
		public float $dividendAmount,
		public float $splitCoefficient,
	) {
	}
}
