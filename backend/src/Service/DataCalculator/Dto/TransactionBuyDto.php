<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use DateTimeImmutable;
use Decimal\Decimal;

class TransactionBuyDto
{
	public function __construct(
		public ?int $brokerId,
		public DateTimeImmutable $actionCreated,
		public Decimal $units,
		public Decimal $priceTickerCurrency,
		public Decimal $priceDefaultCurrency,
		public Decimal $priceWithSplitTickerCurrency,
		public Decimal $priceWithSplitDefaultCurrency,
	) {
	}
}
