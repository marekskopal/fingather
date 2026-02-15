<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class TaxReportDividendsDto
{
	/**
	 * @param list<TaxReportDividendsByCountryDto> $dividendsByCountry
	 * @param list<TaxReportDividendTransactionDto> $transactions
	 */
	public function __construct(
		public Decimal $totalGross,
		public Decimal $totalTax,
		public Decimal $totalNet,
		public array $dividendsByCountry,
		public array $transactions,
	) {
	}
}
