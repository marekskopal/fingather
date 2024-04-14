<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

class TransactionBuyDto
{
	public function __construct(
		public Decimal $units,
		public Decimal $unitsWithSplits,
		public Decimal $splitFactor,
		public Decimal $priceTickerCurrency,
		public Decimal $priceDefaultCurrency,
	) {
	}
}
