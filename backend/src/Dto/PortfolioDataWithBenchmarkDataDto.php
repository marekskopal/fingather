<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\BenchmarkDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\DateTimeUtils;

final readonly class PortfolioDataWithBenchmarkDataDto
{
	public function __construct(
		public string $date,
		public Decimal $value,
		public Decimal $transactionValue,
		public Decimal $gain,
		public float $gainPercentage,
		public Decimal $dividendYield,
		public float $dividendYieldPercentage,
		public Decimal $fxImpact,
		public float $fxImpactPercentage,
		public Decimal $return,
		public float $returnPercentage,
		public ?BenchmarkDataDto $benchmarkData,
	) {
	}

	public static function fromCalculatedDataDto(CalculatedDataDto $portfolioData, ?BenchmarkDataDto $benchmarkData = null): self
	{
		return new self(
			date: DateTimeUtils::formatZulu($portfolioData->date),
			value: $portfolioData->value,
			transactionValue: $portfolioData->transactionValue,
			gain: $portfolioData->gain,
			gainPercentage: $portfolioData->gainPercentage,
			dividendYield: $portfolioData->dividendYield,
			dividendYieldPercentage: $portfolioData->dividendYieldPercentage,
			fxImpact: $portfolioData->fxImpact,
			fxImpactPercentage: $portfolioData->fxImpactPercentage,
			return: $portfolioData->return,
			returnPercentage: $portfolioData->returnPercentage,
			benchmarkData: $benchmarkData,
		);
	}
}
