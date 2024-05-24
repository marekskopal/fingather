<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\AssetWithPropertiesDto;
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
		private readonly AssetProvider $assetProvider,
		private readonly CountryDataProvider $countryDataProvider,
		private readonly AssetDataProvider $assetDataProvider,
	) {
	}

	/** @return list<CountryWithCountryDataDto> */
	public function getCountriesWithCountryData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);

		$countries = [];
		$countryAssets = [];

		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null || $assetData->isClosed()) {
				continue;
			}

			$assetDto = AssetWithPropertiesDto::fromEntity($asset, $assetData);

			$country = $asset->getTicker()->getCountry();

			$countryAssets[$country->getId()][] = $assetDto;

			if (array_key_exists($country->getId(), $countries)) {
				continue;
			}

			$countries[$country->getId()] = $country;
		}

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
