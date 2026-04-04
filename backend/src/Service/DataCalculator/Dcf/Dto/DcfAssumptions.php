<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dcf\Dto;

final readonly class DcfAssumptions
{
	public function __construct(
		public float $wacc,
		public float $terminalGrowthRate,
		public int $projectionYears,
		public ?float $growthRateOverride,
		public ?float $fcfMarginOverride,
		public float $minGrowthRate,
		public float $maxGrowthRate,
	) {
	}

	public static function default(): self
	{
		// TODO(WACC): derive from CAPM (rf + beta * erp) once we wire risk-free rate + ERP config.
		return new self(
			wacc: 0.085,
			terminalGrowthRate: 0.025,
			projectionYears: 5,
			growthRateOverride: null,
			fcfMarginOverride: null,
			minGrowthRate: -0.05,
			maxGrowthRate: 0.30,
		);
	}

	public function with(
		?float $wacc = null,
		?float $terminalGrowthRate = null,
		?int $projectionYears = null,
		?float $growthRateOverride = null,
		?float $fcfMarginOverride = null,
	): self {
		return new self(
			wacc: $wacc ?? $this->wacc,
			terminalGrowthRate: $terminalGrowthRate ?? $this->terminalGrowthRate,
			projectionYears: $projectionYears ?? $this->projectionYears,
			growthRateOverride: $growthRateOverride ?? $this->growthRateOverride,
			fcfMarginOverride: $fcfMarginOverride ?? $this->fcfMarginOverride,
			minGrowthRate: $this->minGrowthRate,
			maxGrowthRate: $this->maxGrowthRate,
		);
	}
}
