<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use DateTimeImmutable;
use Decimal\Decimal;

final readonly class CalculatedDataDto
{
	public function __construct(
		public DateTimeImmutable $date,
		public Decimal $value,
		public Decimal $transactionValue,
		public Decimal $gain,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public Decimal $realizedGain,
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
		public float $twrPercentage = 0.0,
		public float $twrPercentagePerAnnum = 0.0,
		public float $mwrPercentage = 0.0,
	) {
	}

	public function withReturnRates(float $twrPercentage, float $twrPercentagePerAnnum, float $mwrPercentage): self
	{
		return new self(
			date: $this->date,
			value: $this->value,
			transactionValue: $this->transactionValue,
			gain: $this->gain,
			gainPercentage: $this->gainPercentage,
			gainPercentagePerAnnum: $this->gainPercentagePerAnnum,
			realizedGain: $this->realizedGain,
			dividendYield: $this->dividendYield,
			dividendYieldPercentage: $this->dividendYieldPercentage,
			dividendYieldPercentagePerAnnum: $this->dividendYieldPercentagePerAnnum,
			fxImpact: $this->fxImpact,
			fxImpactPercentage: $this->fxImpactPercentage,
			fxImpactPercentagePerAnnum: $this->fxImpactPercentagePerAnnum,
			return: $this->return,
			returnPercentage: $this->returnPercentage,
			returnPercentagePerAnnum: $this->returnPercentagePerAnnum,
			tax: $this->tax,
			fee: $this->fee,
			twrPercentage: $twrPercentage,
			twrPercentagePerAnnum: $twrPercentagePerAnnum,
			mwrPercentage: $mwrPercentage,
		);
	}
}
