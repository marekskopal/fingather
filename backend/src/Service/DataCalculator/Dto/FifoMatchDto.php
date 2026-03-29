<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class FifoMatchDto
{
	public function __construct(public TransactionBuyDto $buy, public Decimal $usedUnitsWithSplits, public Decimal $usedOriginalUnits,)
	{
	}
}
