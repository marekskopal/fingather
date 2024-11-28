<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

class CountryProvider
{
	public function __construct(private readonly AssetProvider $assetProvider,)
	{
	}

	/** @return array<int, Country> */
	public function getCountriesFromAssets(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$countries = [];

		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$country = $asset->ticker->getCountry();

			if (array_key_exists($country->id, $countries)) {
				continue;
			}

			$countries[$country->id] = $country;
		}

		return $countries;
	}
}
