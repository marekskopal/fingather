<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Service\DataCalculator\Dto\TaxReportDividendsByCountryDto;

final readonly class McpTaxDividendCountryDto
{
	public function __construct(
		public string $country,
		public string $isoCode,
		public string $totalGross,
		public string $totalTax,
		public string $totalNet,
	) {
	}

	public static function fromDto(TaxReportDividendsByCountryDto $countryData): self
	{
		return new self(
			country: $countryData->countryName,
			isoCode: $countryData->countryIsoCode,
			totalGross: (string) $countryData->totalGross,
			totalTax: (string) $countryData->totalTax,
			totalNet: (string) $countryData->totalNet,
		);
	}
}
