<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

readonly class GroupDataDto
{
	public function __construct(
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
	) {
	}

	public static function fromCalculatedDataDto(CalculatedDataDto $calculatedDataDto): self
	{
		return new self(
			$calculatedDataDto->value,
			$calculatedDataDto->transactionValue,
			$calculatedDataDto->gain,
			$calculatedDataDto->gainPercentage,
			$calculatedDataDto->gainPercentagePerAnnum,
			$calculatedDataDto->dividendYield,
			$calculatedDataDto->dividendYieldPercentage,
			$calculatedDataDto->dividendYieldPercentagePerAnnum,
			$calculatedDataDto->fxImpact,
			$calculatedDataDto->fxImpactPercentage,
			$calculatedDataDto->fxImpactPercentagePerAnnum,
			$calculatedDataDto->return,
			$calculatedDataDto->returnPercentage,
			$calculatedDataDto->returnPercentagePerAnnum,
		);
	}
}
