<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Ticker;

class SplitFixture
{
	public static function getSplit(?Ticker $ticker = null, ?DateTimeImmutable $date = null, ?Decimal $factor = null,): Split
	{
		return new Split(
			ticker: $ticker ?? TickerFixture::getTicker(),
			date: $date ?? new DateTimeImmutable(),
			factor: $factor ?? new Decimal(1),
		);
	}
}
