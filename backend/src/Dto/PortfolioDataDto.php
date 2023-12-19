<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\PortfolioData;

final readonly class PortfolioDataDto
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

	public static function fromEntity(PortfolioData $portfolioData): self
	{
		return new self(
			id: $portfolioData->getId(),
			value: new Decimal($portfolioData->getValue()),
			transactionValue: new Decimal($portfolioData->getTransactionValue()),
			gain: new Decimal($portfolioData->getGain()),
			gainPercentage: $portfolioData->getGainPercentage(),
			dividendGain: new Decimal($portfolioData->getDividendGain()),
			dividendGainPercentage: $portfolioData->getDividendGainPercentage(),
			fxImpact: new Decimal($portfolioData->getFxImpact()),
			fxImpactPercentage: $portfolioData->getFxImpactPercentage(),
			return: new Decimal($portfolioData->getReturn()),
			returnPercentage: $portfolioData->getReturnPercentage(),
		);
	}
}
