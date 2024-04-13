<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class YearCalculatedDataDto
{
	public function __construct(
		public int $year,
		public Decimal $value,
		public ?Decimal $valueInterannually,
		public Decimal $transactionValue,
		public ?Decimal $transactionValueInterannually,
		public Decimal $gain,
		public ?Decimal $gainInterannually,
		public float $gainPercentage,
		public ?float $gainPercentageInterannually,
		public float $gainPercentagePerAnnum,
		public ?float $gainPercentagePerAnnumInterannually,
		public Decimal $realizedGain,
		public ?Decimal $realizedGainInterannually,
		public Decimal $dividendGain,
		public ?Decimal $dividendGainInterannually,
		public float $dividendGainPercentage,
		public ?float $dividendGainPercentageInterannually,
		public float $dividendGainPercentagePerAnnum,
		public ?float $dividendGainPercentagePerAnnumInterannually,
		public Decimal $fxImpact,
		public ?Decimal $fxImpactInterannually,
		public float $fxImpactPercentage,
		public ?float $fxImpactPercentageInterannually,
		public float $fxImpactPercentagePerAnnum,
		public ?float $fxImpactPercentagePerAnnumInterannually,
		public Decimal $return,
		public ?Decimal $returnInterannually,
		public float $returnPercentage,
		public ?float $returnPercentageInterannually,
		public float $returnPercentagePerAnnum,
		public ?float $returnPercentagePerAnnumInterannually,
		public Decimal $tax,
		public ?Decimal $taxInterannually,
		public Decimal $fee,
		public ?Decimal $feeInterannually,
	) {
	}
}
