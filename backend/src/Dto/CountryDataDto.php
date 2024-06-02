<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\CountryData;

final readonly class CountryDataDto
{
	public function __construct(
		public int $id,
		public Decimal $value,
		public Decimal $transactionValue,
		public Decimal $gain,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public Decimal $dividendYield,
		public float $dividendYieldPercentage,
		public float $dividendYieldPercentagePerAnnum,
		public Decimal $fxImpact,
		public float $fxImpactPercentage,
		public float $fxImpactPercentagePerAnnum,
		public Decimal $return,
		public float $returnPercentage,
		public float $returnPercentagePerAnnum,
	) {
	}

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
