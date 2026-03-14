<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

final readonly class RiskDataDto
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
}
