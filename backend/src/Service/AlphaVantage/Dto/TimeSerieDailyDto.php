<?php

declare(strict_types=1);

namespace FinGather\Service\AlphaVantage\Dto;

use Safe\DateTime;

readonly class TimeSerieDailyDto
{
	public function __construct(
		public DateTime $date,
		public float $open,
		public float $high,
		public float $low,
		public float $close,
		public float $adjustedClose,
		public float $volume,
		public float $dividendAmount,
		public float $splitCoefficient,
	) {
	}
}
