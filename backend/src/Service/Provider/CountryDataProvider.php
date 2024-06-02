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
use FinGather\Utils\DateTimeUtils;

class CountryDataProvider
{
	public function __construct(
		private readonly CountryDataRepository $countryDataRepository,
		private readonly CalculatedDataProvider $calculatedDataProvider,
	) {
	}

	public function getCountryData(Country $country, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CountryData
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$countryData = $this->countryDataRepository->findCountryData($country->getId(), $portfolio->getId(), $dateTime);
		if ($countryData !== null) {
			return $countryData;
		}

		$calculatedData = $this->calculatedDataProvider->getCalculatedDate($user, $portfolio, $dateTime, country: $country);

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
			dividendYield: $calculatedData->dividendYield,
			dividendYieldPercentage: $calculatedData->dividendYieldPercentage,
			dividendYieldPercentagePerAnnum: $calculatedData->dividendYieldPercentagePerAnnum,
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
