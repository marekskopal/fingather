<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;

final readonly class DividendCalendarItemDto
{
	public function __construct(
		public int $assetId,
		public TickerDto $ticker,
		public string $exDate,
		public Decimal $amountPerShare,
		public Decimal $units,
		public Decimal $totalAmount,
		public Decimal $totalAmountDefaultCurrency,
	) {
	}
}
