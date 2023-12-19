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
		public Decimal $dividendGain,
		public float $dividendGainPercentage,
		public Decimal $fxImpact,
		public float $fxImpactPercentage,
		public Decimal $return,
		public float $returnPercentage,
	) {
	}

	public static function fromEntity(GroupData $groupData): self
	{
		return new self(
			id: $groupData->getId(),
			value: new Decimal($groupData->getValue()),
			transactionValue: new Decimal($groupData->getTransactionValue()),
			gain: new Decimal($groupData->getGain()),
			gainPercentage: $groupData->getGainPercentage(),
			dividendGain: new Decimal($groupData->getDividendGain()),
			dividendGainPercentage: $groupData->getDividendGainPercentage(),
			fxImpact: new Decimal($groupData->getFxImpact()),
			fxImpactPercentage: $groupData->getFxImpactPercentage(),
			return: new Decimal($groupData->getReturn()),
			returnPercentage: $groupData->getReturnPercentage(),
		);
	}
}
