<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Ticker;

final readonly class TickerDataAdjustedDto
{
	public function __construct(
		public int $id,
		public Ticker $ticker,
		public DateTimeImmutable $date,
		public Decimal $open,
		public Decimal $close,
		public Decimal $high,
		public Decimal $low,
		public Decimal $volume,
	) {
	}
}
