<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class YearCalculatedDataDto extends CalculatedDataDto
{
	public function __construct(
		public int $year,
		Decimal $value,
		Decimal $transactionValue,
		Decimal $gain,
		float $gainPercentage,
		float $gainPercentagePerAnnum,
		Decimal $realizedGain,
		Decimal $dividendGain,
		float $dividendGainPercentage,
		float $dividendGainPercentagePerAnnum,
		Decimal $fxImpact,
		float $fxImpactPercentage,
		float $fxImpactPercentagePerAnnum,
		Decimal $return,
		float $returnPercentage,
		float $returnPercentagePerAnnum,
		Decimal $tax,
		Decimal $fee,
	) {
		parent::__construct(
			$value,
			$transactionValue,
			$gain,
			$gainPercentage,
			$gainPercentagePerAnnum,
			$realizedGain,
			$dividendGain,
			$dividendGainPercentage,
			$dividendGainPercentagePerAnnum,
			$fxImpact,
			$fxImpactPercentage,
			$fxImpactPercentagePerAnnum,
			$return,
			$returnPercentage,
			$returnPercentagePerAnnum,
			$tax,
			$fee,
		);
	}
}
