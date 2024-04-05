<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\PortfolioData;
use FinGather\Utils\DateTimeUtils;

final readonly class PortfolioDataDto
{
	public function __construct(
		public int $id,
		public string $date,
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
		public Decimal $tax,
		public Decimal $fee,
	) {
	}

	public static function fromEntity(PortfolioData $portfolioData): self
	{
		return new self(
			id: $portfolioData->getId(),
			date: DateTimeUtils::formatZulu($portfolioData->getDate()),
			value: $portfolioData->getValue(),
			transactionValue: $portfolioData->getTransactionValue(),
			gain: $portfolioData->getGain(),
			gainPercentage: $portfolioData->getGainPercentage(),
			gainPercentagePerAnnum: $portfolioData->getGainPercentagePerAnnum(),
			dividendGain: $portfolioData->getDividendGain(),
			dividendGainPercentage: $portfolioData->getDividendGainPercentage(),
			dividendGainPercentagePerAnnum: $portfolioData->getDividendGainPercentagePerAnnum(),
			fxImpact: $portfolioData->getFxImpact(),
			fxImpactPercentage: $portfolioData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $portfolioData->getFxImpactPercentagePerAnnum(),
			return: $portfolioData->getReturn(),
			returnPercentage: $portfolioData->getReturnPercentage(),
			returnPercentagePerAnnum: $portfolioData->getReturnPercentagePerAnnum(),
			tax: $portfolioData->getTax(),
			fee: $portfolioData->getFee(),
		);
	}
}
