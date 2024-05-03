<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Ticker;

final class TickerFixture
{
	public static function getTicker(
		?string $ticker = null,
		?string $name = null,
		?Market $market = null,
		?Currency $currency = null,
		?TickerTypeEnum $type = null,
		?string $isin = null,
		?string $logo = null,
		?string $sector = null,
		?string $industry = null,
		?string $description = null,
		?string $website = null,
		?string $country = null,
	): Ticker {
		return new Ticker(
			ticker: $ticker ?? 'AAPL',
			name: $name ?? 'Apple Inc.',
			market: $market ?? MarketFixture::getMarket(),
			currency: $currency ?? CurrencyFixture::getCurrency(),
			type: $type ?? TickerTypeEnum::Stock,
			isin: $isin ?? 'US0378331005',
			logo: $logo ?? 'https://logo.com',
			sector: $sector ?? 'Technology',
			industry: $industry ?? 'Consumer Electronics',
			description: $description ?? 'Apple Inc. designs, manufactures, and markets smartphones, personal computers, tablets, wearables, and accessories worldwide.',
			website: $website ?? 'https://www.apple.com',
			country: $country ?? 'US',
		);
	}
}
