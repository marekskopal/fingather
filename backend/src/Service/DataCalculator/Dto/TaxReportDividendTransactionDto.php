<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class TaxReportDividendTransactionDto
{
	public function __construct(
		public string $tickerTicker,
		public string $tickerName,
		public string $countryName,
		public string $countryIsoCode,
		public string $date,
		public Decimal $grossAmount,
		public Decimal $tax,
		public Decimal $netAmount,
	) {
	}
}
