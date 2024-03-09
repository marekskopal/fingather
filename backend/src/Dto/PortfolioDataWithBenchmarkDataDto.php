<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\BenchmarkData;
use FinGather\Model\Entity\PortfolioData;
use FinGather\Utils\DateTimeUtils;

final readonly class PortfolioDataWithBenchmarkDataDto
{
	public function __construct(
		public int $id,
		public string $date,
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
		public ?BenchmarkDataDto $benchmarkData,
	) {
	}

	public static function fromEntity(PortfolioData $portfolioData, ?BenchmarkData $benchmarkData = null): self
	{
		return new self(
			id: $portfolioData->getId(),
			date: DateTimeUtils::formatZulu($portfolioData->getDate()),
			value: $portfolioData->getValue(),
			transactionValue: $portfolioData->getTransactionValue(),
			gain: $portfolioData->getGain(),
			gainPercentage: $portfolioData->getGainPercentage(),
			dividendGain: $portfolioData->getDividendGain(),
			dividendGainPercentage: $portfolioData->getDividendGainPercentage(),
			fxImpact: $portfolioData->getFxImpact(),
			fxImpactPercentage: $portfolioData->getFxImpactPercentage(),
			return: $portfolioData->getReturn(),
			returnPercentage: $portfolioData->getReturnPercentage(),
			benchmarkData: $benchmarkData !== null ? BenchmarkDataDto::fromEntity($benchmarkData) : null,
		);
	}
}
