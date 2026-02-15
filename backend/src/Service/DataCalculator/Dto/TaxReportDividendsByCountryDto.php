<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class TaxReportDividendsByCountryDto
{
	public function __construct(
		public string $countryName,
		public string $countryIsoCode,
		public Decimal $totalGross,
		public Decimal $totalTax,
		public Decimal $totalNet,
	) {
	}
}
