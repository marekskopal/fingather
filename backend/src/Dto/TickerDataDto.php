<?php

declare(strict_types=1);

namespace FinGather\Dto;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\TickerData;

final readonly class TickerDataDto
{
	public function __construct(
		public int $id,
		public int $tickerId,
		public DateTimeImmutable $date,
		public Decimal $open,
		public Decimal $close,
		public Decimal $high,
		public Decimal $low,
		public int $volume,
		public float $performance,
	) {
	}

	public static function fromEntity(TickerData $tickerData): self
	{
		return new self(
			id: $tickerData->getId(),
			tickerId: $tickerData->getTicker()->getId(),
			date: $tickerData->getDate(),
			open: new Decimal($tickerData->getOpen()),
			close: new Decimal($tickerData->getClose()),
			high: new Decimal($tickerData->getHigh()),
			low: new Decimal($tickerData->getLow()),
			volume: $tickerData->getVolume(),
			performance: $tickerData->getPerformance(),
		);
	}
}
