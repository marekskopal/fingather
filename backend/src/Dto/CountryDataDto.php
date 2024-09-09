<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\CountryData;

final readonly class CountryDataDto extends AbstractGroupDataDto
{
	public static function fromEntity(CountryData $countryData): self
	{
		return new self(
			id: $countryData->getId(),
			value: $countryData->getValue(),
			transactionValue: $countryData->getTransactionValue(),
			gain: $countryData->getGain(),
			gainPercentage: $countryData->getGainPercentage(),
			gainPercentagePerAnnum: $countryData->getGainPercentagePerAnnum(),
			dividendYield: $countryData->getdividendYield(),
			dividendYieldPercentage: $countryData->getdividendYieldPercentage(),
			dividendYieldPercentagePerAnnum: $countryData->getdividendYieldPercentagePerAnnum(),
			fxImpact: $countryData->getFxImpact(),
			fxImpactPercentage: $countryData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $countryData->getFxImpactPercentagePerAnnum(),
			return: $countryData->getReturn(),
			returnPercentage: $countryData->getReturnPercentage(),
			returnPercentagePerAnnum: $countryData->getReturnPercentagePerAnnum(),
		);
	}
}
