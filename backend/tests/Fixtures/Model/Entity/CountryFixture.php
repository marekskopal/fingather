<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Country;

final class CountryFixture
{
	public static function getCountry(
		?int $id = null,
		?string $isoCode = null,
		?string $isoCode3 = null,
		?string $name = null,
		?bool $isOthers = null,
	): Country
	{
		$country = new Country(
			isoCode: $isoCode ?? 'US',
			isoCode3: $isoCode3 ?? 'USA',
			name: $name ?? 'United States',
			isOthers: $isOthers ?? false,
		);

		$country->id = $id ?? 1;

		return $country;
	}
}
