<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Service\DataCalculator\Dcf\Dto\DcfResult;

final readonly class TickerDcfValuationAssumptionsDto
{
	public function __construct(
		public float $wacc,
		public float $terminalGrowthRate,
		public int $projectionYears,
		public float $appliedGrowthRate,
		public float $appliedFcfMargin,
	) {
	}

	public static function fromResult(DcfResult $result): self
	{
		return new self(
			wacc: $result->assumptions->wacc,
			terminalGrowthRate: $result->assumptions->terminalGrowthRate,
			projectionYears: $result->assumptions->projectionYears,
			appliedGrowthRate: $result->appliedGrowthRate,
			appliedFcfMargin: $result->appliedFcfMargin,
		);
	}
}
