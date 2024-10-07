<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\DateTimeUtils;

final readonly class PortfolioDataDto
{
	public function __construct(
		public string $date,
		public Decimal $value,
		public Decimal $transactionValue,
		public Decimal $gain,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public Decimal $dividendYield,
		public float $dividendYieldPercentage,
		public float $dividendYieldPercentagePerAnnum,
		public Decimal $fxImpact,
		public float $fxImpactPercentage,
		public float $fxImpactPercentagePerAnnum,
		public Decimal $return,
		public float $returnPercentage,
		public float $returnPercentagePerAnnum,
		public Decimal $tax,
		public Decimal $fee,
		public Decimal $realizedGain,
	) {
	}

	public static function fromCalculatedDataDto(CalculatedDataDto $calculatedData): self
	{
		return new self(
			date: DateTimeUtils::formatZulu($calculatedData->date),
			value: $calculatedData->value,
			transactionValue: $calculatedData->transactionValue,
			gain: $calculatedData->gain,
			gainPercentage: $calculatedData->gainPercentage,
			gainPercentagePerAnnum: $calculatedData->gainPercentagePerAnnum,
			realizedGain: $calculatedData->realizedGain,
			dividendYield: $calculatedData->dividendYield,
			dividendYieldPercentage: $calculatedData->dividendYieldPercentage,
			dividendYieldPercentagePerAnnum: $calculatedData->dividendYieldPercentagePerAnnum,
			fxImpact: $calculatedData->fxImpact,
			fxImpactPercentage: $calculatedData->fxImpactPercentage,
			fxImpactPercentagePerAnnum: $calculatedData->fxImpactPercentagePerAnnum,
			return: $calculatedData->return,
			returnPercentage: $calculatedData->returnPercentage,
			returnPercentagePerAnnum: $calculatedData->returnPercentagePerAnnum,
			tax: $calculatedData->tax,
			fee: $calculatedData->fee,
		);
	}
}
