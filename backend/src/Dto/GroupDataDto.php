<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\GroupData;

final readonly class GroupDataDto
{
	public function __construct(
		public int $id,
		public Decimal $value,
		public Decimal $transactionValue,
		public Decimal $gain,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public Decimal $dividendGain,
		public float $dividendGainPercentage,
		public float $dividendGainPercentagePerAnnum,
		public Decimal $fxImpact,
		public float $fxImpactPercentage,
		public float $fxImpactPercentagePerAnnum,
		public Decimal $return,
		public float $returnPercentage,
		public float $returnPercentagePerAnnum,
	) {
	}

	public static function fromEntity(GroupData $groupData): self
	{
		return new self(
			id: $groupData->getId(),
			value: $groupData->getValue(),
			transactionValue: $groupData->getTransactionValue(),
			gain: $groupData->getGain(),
			gainPercentage: $groupData->getGainPercentage(),
			gainPercentagePerAnnum: $groupData->getGainPercentagePerAnnum(),
			dividendGain: $groupData->getDividendGain(),
			dividendGainPercentage: $groupData->getDividendGainPercentage(),
			dividendGainPercentagePerAnnum: $groupData->getDividendGainPercentagePerAnnum(),
			fxImpact: $groupData->getFxImpact(),
			fxImpactPercentage: $groupData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $groupData->getFxImpactPercentagePerAnnum(),
			return: $groupData->getReturn(),
			returnPercentage: $groupData->getReturnPercentage(),
			returnPercentagePerAnnum: $groupData->getReturnPercentagePerAnnum(),
		);
	}
}
