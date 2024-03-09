<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\TickerData;
use FinGather\Service\Provider\Dto\TickerDataAdjustedDto;
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
	) {
	}

	public static function fromEntity(TickerData $tickerData): self
	{
		return new self(
			id: $tickerData->getId(),
			tickerId: $tickerData->getTicker()->getId(),
			date: DateTimeUtils::formatZulu($tickerData->getDate()),
			open: $tickerData->getOpen(),
			close: $tickerData->getClose(),
			high: $tickerData->getHigh(),
			low: $tickerData->getLow(),
			volume: $tickerData->getVolume(),
		);
	}

	public static function fromTickerDataAdjusted(TickerDataAdjustedDto $tickerData): self
	{
		return new self(
			id: $tickerData->id,
			tickerId: $tickerData->ticker->getId(),
			date: DateTimeUtils::formatZulu($tickerData->date),
			open: $tickerData->open,
			close: $tickerData->close,
			high: $tickerData->high,
			low: $tickerData->low,
			volume: $tickerData->volume,
		);
	}
}
