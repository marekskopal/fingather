<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

final readonly class SimulationResultDto
{
	/**
	 * @param list<float> $p10
	 * @param list<float> $p50
	 * @param list<float> $p90
	 */
	public function __construct(public array $p10, public array $p50, public array $p90,)
	{
	}
}
