<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\CountryDataDto;
use FinGather\Dto\CountryWithCountryDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Utils\CalculatorUtils;
use Safe\DateTimeImmutable;

class CountryWithCountryDataProvider
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly CountryProvider $countryProvider,
		private readonly CountryDataProvider $countryDataProvider,
	) {
	}

	/** @return list<CountryWithCountryDataDto> */
	public function getCountriesWithCountryData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);

		$countries = $this->countryProvider->getCountriesFromAssets($user, $portfolio, $dateTime);

		$countriesWithCountryData = [];

		foreach	($countries as $countryId => $country) {
			$countryData = $this->countryDataProvider->getCountryData($country, $user, $portfolio, $dateTime);

			$countriesWithCountryData[] = new CountryWithCountryDataDto(
				id: $countryId,
				userId: $user->getId(),
				name: $country->getName(),
				percentage: CalculatorUtils::toPercentage($countryData->getValue(), $portfolioData->getValue()),
				countryData: CountryDataDto::fromEntity($countryData),
			);
		}

		return $countriesWithCountryData;
	}
}
