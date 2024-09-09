<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\GroupData;

final readonly class GroupDataDto extends AbstractGroupDataDto
{
	public static function fromEntity(GroupData $groupData): self
	{
		return new self(
			id: $groupData->getId(),
			value: $groupData->getValue(),
			transactionValue: $groupData->getTransactionValue(),
			gain: $groupData->getGain(),
			gainPercentage: $groupData->getGainPercentage(),
			gainPercentagePerAnnum: $groupData->getGainPercentagePerAnnum(),
			dividendYield: $groupData->getdividendYield(),
			dividendYieldPercentage: $groupData->getdividendYieldPercentage(),
			dividendYieldPercentagePerAnnum: $groupData->getdividendYieldPercentagePerAnnum(),
			fxImpact: $groupData->getFxImpact(),
			fxImpactPercentage: $groupData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $groupData->getFxImpactPercentagePerAnnum(),
			return: $groupData->getReturn(),
			returnPercentage: $groupData->getReturnPercentage(),
			returnPercentagePerAnnum: $groupData->getReturnPercentagePerAnnum(),
		);
	}
}
