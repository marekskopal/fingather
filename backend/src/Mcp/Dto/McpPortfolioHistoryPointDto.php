<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

final readonly class McpPortfolioHistoryPointDto
{
	public function __construct(
		public string $date,
		public string $value,
		public string $transactionValue,
		public string $gain,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public string $dividendYield,
		public float $dividendYieldPercentage,
		public string $fxImpact,
		public float $fxImpactPercentage,
		public string $return,
		public float $returnPercentage,
		public float $returnPercentagePerAnnum,
	) {
	}

	public static function fromCalculatedData(CalculatedDataDto $data): self
	{
		return new self(
			date: $data->date->format('Y-m-d'),
			value: (string) $data->value,
			transactionValue: (string) $data->transactionValue,
			gain: (string) $data->gain,
			gainPercentage: $data->gainPercentage,
			gainPercentagePerAnnum: $data->gainPercentagePerAnnum,
			dividendYield: (string) $data->dividendYield,
			dividendYieldPercentage: $data->dividendYieldPercentage,
			fxImpact: (string) $data->fxImpact,
			fxImpactPercentage: $data->fxImpactPercentage,
			return: (string) $data->return,
			returnPercentage: $data->returnPercentage,
			returnPercentagePerAnnum: $data->returnPercentagePerAnnum,
		);
	}
}
