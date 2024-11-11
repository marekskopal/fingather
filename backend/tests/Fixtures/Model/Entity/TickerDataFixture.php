<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;

final class TickerDataFixture
{
	/** @api */
	public static function getTickerData(
		?Ticker $ticker = null,
		?DateTimeImmutable $date = null,
		?Decimal $open = null,
		?Decimal $high = null,
		?Decimal $low = null,
		?Decimal $close = null,
		?Decimal $volume = null,
	): TickerData {
		return new TickerData(
			ticker: $ticker ?? TickerFixture::getTicker(),
			date: $date ?? new DateTimeImmutable(),
			open: $open ?? new Decimal(10),
			high: $high ?? new Decimal(20),
			low: $low ?? new Decimal(5),
			close: $close ?? new Decimal(10),
			volume: $volume ?? new Decimal(1000),
		);
	}
}
