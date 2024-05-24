<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\Database\Exception\StatementException\ConstrainException;
use DateTimeImmutable;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\CountryData;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\CountryDataRepository;
use FinGather\Service\DataCalculator\DataCalculator;
use FinGather\Utils\DateTimeUtils;

class CountryDataProvider
{
	public function __construct(
		private readonly CountryDataRepository $countryDataRepository,
		private readonly DataCalculator $dataCalculator,
		private readonly AssetProvider $assetProvider,
		private readonly AssetDataProvider $assetDataProvider,
	) {
	}

	public function getCountryData(Country $country, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CountryData
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$countryData = $this->countryDataRepository->findCountryData($country->getId(), $portfolio->getId(), $dateTime);
		if ($countryData !== null) {
			return $countryData;
		}

		$assetDatas = [];

		$firstTransactionActionCreated = $dateTime;

		$assets = $this->assetProvider->getAssets($user, $portfolio, $dateTime, country: $country);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null) {
				continue;
			}

			if ($firstTransactionActionCreated > $assetData->getFirstTransactionActionCreated()) {
				$firstTransactionActionCreated = $assetData->getFirstTransactionActionCreated();
			}

			$assetDatas[] = $assetData;
		}

		$calculatedData = $this->dataCalculator->calculate($assetDatas, $dateTime, $firstTransactionActionCreated);

		$countryData = new CountryData(
			country: $country,
			user: $user,
			portfolio: $portfolio,
			date: $dateTime,
			value: $calculatedData->value,
			transactionValue: $calculatedData->transactionValue,
			gain: $calculatedData->gain,
			gainPercentage: $calculatedData->gainPercentage,
			gainPercentagePerAnnum: $calculatedData->gainPercentagePerAnnum,
			realizedGain: $calculatedData->realizedGain,
			dividendGain: $calculatedData->dividendGain,
			dividendGainPercentage: $calculatedData->dividendGainPercentage,
			dividendGainPercentagePerAnnum: $calculatedData->dividendGainPercentagePerAnnum,
			fxImpact: $calculatedData->fxImpact,
			fxImpactPercentage: $calculatedData->fxImpactPercentage,
			fxImpactPercentagePerAnnum: $calculatedData->fxImpactPercentagePerAnnum,
			return: $calculatedData->return,
			returnPercentage: $calculatedData->returnPercentage,
			returnPercentagePerAnnum: $calculatedData->returnPercentagePerAnnum,
			tax: $calculatedData->tax,
			fee: $calculatedData->fee,
		);

		try {
			$this->countryDataRepository->persist($countryData);
		} catch (ConstrainException) {
			$countryData = $this->countryDataRepository->findCountryData($country->getId(), $country->getId(), $dateTime);
			assert($countryData instanceof CountryData);
		}

		return $countryData;
	}

	public function deleteUserCountryData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->countryDataRepository->deleteUserCountryData($user?->getId(), $portfolio?->getId(), $date);
	}
}
