<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\IndustryData;

final readonly class IndustryDataDto
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

	public static function fromEntity(IndustryData $industryData): self
	{
		return new self(
			id: $industryData->getId(),
			value: $industryData->getValue(),
			transactionValue: $industryData->getTransactionValue(),
			gain: $industryData->getGain(),
			gainPercentage: $industryData->getGainPercentage(),
			gainPercentagePerAnnum: $industryData->getGainPercentagePerAnnum(),
			dividendYield: $industryData->getdividendYield(),
			dividendYieldPercentage: $industryData->getdividendYieldPercentage(),
			dividendYieldPercentagePerAnnum: $industryData->getdividendYieldPercentagePerAnnum(),
			fxImpact: $industryData->getFxImpact(),
			fxImpactPercentage: $industryData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $industryData->getFxImpactPercentagePerAnnum(),
			return: $industryData->getReturn(),
			returnPercentage: $industryData->getReturnPercentage(),
			returnPercentagePerAnnum: $industryData->getReturnPercentagePerAnnum(),
		);
	}
}
