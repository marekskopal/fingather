<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

final readonly class TickerWeightDto
{
	public function __construct(public int $tickerId, public float $weight)
	{
	}
}
