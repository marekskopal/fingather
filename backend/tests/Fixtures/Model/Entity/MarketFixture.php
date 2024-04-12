<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Market;

class MarketFixture
{
	public static function getMarket(
		?MarketTypeEnum $type = null,
		?string $name = null,
		?string $acronym = null,
		?string $mic = null,
		?string $country = null,
		?string $city = null,
		?string $timezone = null,
		?Currency $currency = null,
	): Market {
		return new Market(
			type: $type ?? MarketTypeEnum::Stock,
			name: $name ?? 'New York Stock Exchange',
			acronym: $acronym ?? 'NYSE',
			mic: $mic ?? 'XNYS',
			country: $country ?? 'US',
			city: $city ?? 'New York',
			timezone: $timezone ?? 'America/New_York',
			currency: $currency ?? CurrencyFixture::getCurrency(),
		);
	}
}
