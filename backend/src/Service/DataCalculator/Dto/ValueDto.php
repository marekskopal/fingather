<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class ValueDto
{
	public function __construct(public Decimal $value, public Decimal $valueDefaultCurrency)
	{
	}
}
