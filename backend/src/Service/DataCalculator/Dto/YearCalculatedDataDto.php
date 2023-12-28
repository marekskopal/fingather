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
		Decimal $dividendGain,
		float $dividendGainPercentage,
		Decimal $fxImpact,
		float $fxImpactPercentage,
		Decimal $return,
		float $returnPercentage,
	) {
		parent::__construct(
			$value,
			$transactionValue,
			$gain,
			$gainPercentage,
			$dividendGain,
			$dividendGainPercentage,
			$fxImpact,
			$fxImpactPercentage,
			$return,
			$returnPercentage,
		);
	}
}
