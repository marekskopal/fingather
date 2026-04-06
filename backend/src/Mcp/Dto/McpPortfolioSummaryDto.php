<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Portfolio;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

final readonly class McpPortfolioSummaryDto
{
	public function __construct(
		public int $portfolioId,
		public string $name,
		public string $currency,
		public string $value,
		public string $transactionValue,
		public string $gain,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public string $return,
		public float $returnPercentage,
		public float $returnPercentagePerAnnum,
		public string $dividendYield,
		public float $dividendYieldPercentage,
		public string $realizedGain,
		public string $fxImpact,
		public float $fxImpactPercentage,
		public string $tax,
		public string $fee,
	) {
	}

	public static function fromPortfolioData(Portfolio $portfolio, CalculatedDataDto $data): self
	{
		return new self(
			portfolioId: $portfolio->id,
			name: $portfolio->name,
			currency: $portfolio->currency->code,
			value: (string) $data->value,
			transactionValue: (string) $data->transactionValue,
			gain: (string) $data->gain,
			gainPercentage: $data->gainPercentage,
			gainPercentagePerAnnum: $data->gainPercentagePerAnnum,
			return: (string) $data->return,
			returnPercentage: $data->returnPercentage,
			returnPercentagePerAnnum: $data->returnPercentagePerAnnum,
			dividendYield: (string) $data->dividendYield,
			dividendYieldPercentage: $data->dividendYieldPercentage,
			realizedGain: (string) $data->realizedGain,
			fxImpact: (string) $data->fxImpact,
			fxImpactPercentage: $data->fxImpactPercentage,
			tax: (string) $data->tax,
			fee: (string) $data->fee,
		);
	}
}
