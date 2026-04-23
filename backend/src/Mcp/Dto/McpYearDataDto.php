<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Service\DataCalculator\Dto\YearCalculatedDataDto;

final readonly class McpYearDataDto
{
	public function __construct(
		public int $year,
		public string $value,
		public ?string $valueChange,
		public string $transactionValue,
		public ?string $transactionValueChange,
		public string $gain,
		public ?string $gainChange,
		public float $gainPercentage,
		public ?float $gainPercentageChange,
		public float $gainPercentagePerAnnum,
		public ?float $gainPercentagePerAnnumChange,
		public string $realizedGain,
		public ?string $realizedGainChange,
		public string $dividendYield,
		public ?string $dividendYieldChange,
		public float $dividendYieldPercentage,
		public ?float $dividendYieldPercentageChange,
		public float $dividendYieldPercentagePerAnnum,
		public ?float $dividendYieldPercentagePerAnnumChange,
		public string $fxImpact,
		public ?string $fxImpactChange,
		public float $fxImpactPercentage,
		public ?float $fxImpactPercentageChange,
		public string $return,
		public ?string $returnChange,
		public float $returnPercentage,
		public ?float $returnPercentageChange,
		public float $returnPercentagePerAnnum,
		public ?float $returnPercentagePerAnnumChange,
		public string $tax,
		public ?string $taxChange,
		public string $fee,
		public ?string $feeChange,
	) {
	}

	public static function fromYearCalculatedData(YearCalculatedDataDto $data): self
	{
		return new self(
			year: $data->year,
			value: (string) $data->value,
			valueChange: $data->valueInterannually !== null ? (string) $data->valueInterannually : null,
			transactionValue: (string) $data->transactionValue,
			transactionValueChange: $data->transactionValueInterannually !== null ? (string) $data->transactionValueInterannually : null,
			gain: (string) $data->gain,
			gainChange: $data->gainInterannually !== null ? (string) $data->gainInterannually : null,
			gainPercentage: $data->gainPercentage,
			gainPercentageChange: $data->gainPercentageInterannually,
			gainPercentagePerAnnum: $data->gainPercentagePerAnnum,
			gainPercentagePerAnnumChange: $data->gainPercentagePerAnnumInterannually,
			realizedGain: (string) $data->realizedGain,
			realizedGainChange: $data->realizedGainInterannually !== null ? (string) $data->realizedGainInterannually : null,
			dividendYield: (string) $data->dividendYield,
			dividendYieldChange: $data->dividendYieldInterannually !== null ? (string) $data->dividendYieldInterannually : null,
			dividendYieldPercentage: $data->dividendYieldPercentage,
			dividendYieldPercentageChange: $data->dividendYieldPercentageInterannually,
			dividendYieldPercentagePerAnnum: $data->dividendYieldPercentagePerAnnum,
			dividendYieldPercentagePerAnnumChange: $data->dividendYieldPercentagePerAnnumInterannually,
			fxImpact: (string) $data->fxImpact,
			fxImpactChange: $data->fxImpactInterannually !== null ? (string) $data->fxImpactInterannually : null,
			fxImpactPercentage: $data->fxImpactPercentage,
			fxImpactPercentageChange: $data->fxImpactPercentageInterannually,
			return: (string) $data->return,
			returnChange: $data->returnInterannually !== null ? (string) $data->returnInterannually : null,
			returnPercentage: $data->returnPercentage,
			returnPercentageChange: $data->returnPercentageInterannually,
			returnPercentagePerAnnum: $data->returnPercentagePerAnnum,
			returnPercentagePerAnnumChange: $data->returnPercentagePerAnnumInterannually,
			tax: (string) $data->tax,
			taxChange: $data->taxInterannually !== null ? (string) $data->taxInterannually : null,
			fee: (string) $data->fee,
			feeChange: $data->feeInterannually !== null ? (string) $data->feeInterannually : null,
		);
	}
}
