<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

final readonly class OverviewYearDto extends CalculatedDataDto
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
		float $performance
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
			$performance
		);
	}
}
