<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Service\DataCalculator\Dto\RiskDataDto;

final readonly class PortfolioRiskDataDto
{
	public function __construct(
		public float $volatility,
		public float $maxDrawdown,
		public float $sharpeRatio,
		public float $beta,
		/** @var list<string> */
		public array $correlationLabels,
		/** @var list<list<float>> */
		public array $correlationMatrix,
	) {
	}

	public static function fromRiskDataDto(RiskDataDto $riskDataDto): self
	{
		return new self(
			volatility: $riskDataDto->volatility,
			maxDrawdown: $riskDataDto->maxDrawdown,
			sharpeRatio: $riskDataDto->sharpeRatio,
			beta: $riskDataDto->beta,
			correlationLabels: $riskDataDto->correlationLabels,
			correlationMatrix: $riskDataDto->correlationMatrix,
		);
	}
}
