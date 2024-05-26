<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\SectorData;

final readonly class SectorDataDto
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

	public static function fromEntity(SectorData $sectorData): self
	{
		return new self(
			id: $sectorData->getId(),
			value: $sectorData->getValue(),
			transactionValue: $sectorData->getTransactionValue(),
			gain: $sectorData->getGain(),
			gainPercentage: $sectorData->getGainPercentage(),
			gainPercentagePerAnnum: $sectorData->getGainPercentagePerAnnum(),
			dividendGain: $sectorData->getDividendGain(),
			dividendGainPercentage: $sectorData->getDividendGainPercentage(),
			dividendGainPercentagePerAnnum: $sectorData->getDividendGainPercentagePerAnnum(),
			fxImpact: $sectorData->getFxImpact(),
			fxImpactPercentage: $sectorData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $sectorData->getFxImpactPercentagePerAnnum(),
			return: $sectorData->getReturn(),
			returnPercentage: $sectorData->getReturnPercentage(),
			returnPercentagePerAnnum: $sectorData->getReturnPercentagePerAnnum(),
		);
	}
}
