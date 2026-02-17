<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use DateTimeImmutable;
use Decimal\Decimal;

final class TransactionBuyDto
{
	public function __construct(
		public readonly ?int $brokerId,
		public readonly DateTimeImmutable $actionCreated,
		public Decimal $units,
		public readonly Decimal $priceTickerCurrency,
		public readonly Decimal $priceDefaultCurrency,
		public readonly Decimal $priceWithSplitTickerCurrency,
		public readonly Decimal $priceWithSplitDefaultCurrency,
	) {
	}
}
