<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\TickerData;
use FinGather\Utils\DateTimeUtils;

final readonly class TickerDataDto
{
	public function __construct(
		public int $id,
		public int $tickerId,
		public string $date,
		public Decimal $open,
		public Decimal $close,
		public Decimal $high,
		public Decimal $low,
		public Decimal $volume,
		public float $performance,
	) {
	}

	public static function fromEntity(TickerData $tickerData): self
	{
		return new self(
			id: $tickerData->getId(),
			tickerId: $tickerData->getTicker()->getId(),
			date: DateTimeUtils::formatZulu($tickerData->getDate()),
			open: new Decimal($tickerData->getOpen()),
			close: new Decimal($tickerData->getClose()),
			high: new Decimal($tickerData->getHigh()),
			low: new Decimal($tickerData->getLow()),
			volume: new Decimal($tickerData->getVolume()),
			performance: $tickerData->getPerformance(),
		);
	}
}
