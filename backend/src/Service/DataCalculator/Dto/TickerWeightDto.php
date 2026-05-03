<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use FinGather\Model\Entity\Enum\TickerTypeEnum;

final readonly class TickerWeightDto
{
	public function __construct(public int $tickerId, public float $weight, public ?TickerTypeEnum $type = null,)
	{
	}
}
