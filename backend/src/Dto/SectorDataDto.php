<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\SectorData;

final readonly class SectorDataDto extends AbstractGroupDataDto
{
	public static function fromEntity(SectorData $sectorData): self
	{
		return new self(
			id: $sectorData->getId(),
			value: $sectorData->getValue(),
			transactionValue: $sectorData->getTransactionValue(),
			gain: $sectorData->getGain(),
			gainPercentage: $sectorData->getGainPercentage(),
			gainPercentagePerAnnum: $sectorData->getGainPercentagePerAnnum(),
			dividendYield: $sectorData->getdividendYield(),
			dividendYieldPercentage: $sectorData->getdividendYieldPercentage(),
			dividendYieldPercentagePerAnnum: $sectorData->getdividendYieldPercentagePerAnnum(),
			fxImpact: $sectorData->getFxImpact(),
			fxImpactPercentage: $sectorData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $sectorData->getFxImpactPercentagePerAnnum(),
			return: $sectorData->getReturn(),
			returnPercentage: $sectorData->getReturnPercentage(),
			returnPercentagePerAnnum: $sectorData->getReturnPercentagePerAnnum(),
		);
	}
}
