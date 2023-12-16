<?php

declare(strict_types=1);

namespace FinGather\Service\AlphaVantage\Dto;

use Brick\Math\BigDecimal;
use Safe\DateTimeImmutable;

readonly class TimeSerieDailyDto
{
	public function __construct(
		public DateTimeImmutable $date,
		public BigDecimal $open,
		public BigDecimal $high,
		public BigDecimal $low,
		public BigDecimal $close,
		public BigDecimal $adjustedClose,
		public int $volume,
		public BigDecimal $dividendAmount,
		public BigDecimal $splitCoefficient,
	) {
	}
}
