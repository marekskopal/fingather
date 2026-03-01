<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

final readonly class ReturnRateDto
{
	public function __construct(public float $annual, public float $monthly)
	{
	}
}
