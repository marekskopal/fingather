<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use DateTimeImmutable;
use Decimal\Decimal;

class TransactionBuyDto
{
	public function __construct(
		public readonly ?int $brokerId,
		public readonly DateTimeImmutable $actionCreated,
		//@phpstan-ignore-next-line
		public Decimal $units,
		public readonly Decimal $priceTickerCurrency,
		public readonly Decimal $priceDefaultCurrency,
		public readonly Decimal $priceWithSplitTickerCurrency,
		public readonly Decimal $priceWithSplitDefaultCurrency,
	) {
	}
}
