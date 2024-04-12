<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Ticker;

class TickerFixture
{
	public static function getTicker(
		?string $ticker = null,
		?string $name = null,
		?Market $market = null,
		?Currency $currency = null,
		?string $logo = null,
	): Ticker {
		return new Ticker(
			ticker: $ticker ?? 'AAPL',
			name: $name ?? 'Apple Inc.',
			market: $market ?? MarketFixture::getMarket(),
			currency: $currency ?? CurrencyFixture::getCurrency(),
			logo: $logo ?? 'https://logo.com',
		);
	}
}
