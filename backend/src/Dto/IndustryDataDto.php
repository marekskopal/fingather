<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\IndustryData;

final readonly class IndustryDataDto extends AbstractGroupDataDto
{
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
