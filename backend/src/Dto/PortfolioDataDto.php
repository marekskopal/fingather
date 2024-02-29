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
	) {
	}

	public static function fromEntity(PortfolioData $portfolioData): self
	{
		return new self(
			id: $portfolioData->getId(),
			date: DateTimeUtils::formatZulu($portfolioData->getDate()),
			value: new Decimal($portfolioData->getValue()),
			transactionValue: new Decimal($portfolioData->getTransactionValue()),
			gain: new Decimal($portfolioData->getGain()),
			gainPercentage: $portfolioData->getGainPercentage(),
			gainPercentagePerAnnum: $portfolioData->getGainPercentagePerAnnum(),
			dividendGain: new Decimal($portfolioData->getDividendGain()),
			dividendGainPercentage: $portfolioData->getDividendGainPercentage(),
			dividendGainPercentagePerAnnum: $portfolioData->getDividendGainPercentagePerAnnum(),
			fxImpact: new Decimal($portfolioData->getFxImpact()),
			fxImpactPercentage: $portfolioData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $portfolioData->getFxImpactPercentagePerAnnum(),
			return: new Decimal($portfolioData->getReturn()),
			returnPercentage: $portfolioData->getReturnPercentage(),
			returnPercentagePerAnnum: $portfolioData->getReturnPercentagePerAnnum(),
		);
	}
}
