<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Portfolio;
use FinGather\Service\DataCalculator\Dto\TaxReportDividendsByCountryDto;
use FinGather\Service\DataCalculator\Dto\TaxReportDto;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainTransactionDto;

final readonly class McpTaxReportDto
{
	/**
	 * @param array{costBasisMethod: string, totalGains: string, totalLosses: string, netRealizedGainLoss: string, totalSalesProceeds: string, totalCostBasis: string, totalFees: string, transactions: list<McpTaxRealizedTransactionDto>} $realizedGains
	 * @param array{totalGross: string, totalTax: string, totalNet: string, byCountry: list<McpTaxDividendCountryDto>} $dividends
	 */
	public function __construct(
		public int $year,
		public string $currency,
		public array $realizedGains,
		public array $dividends,
		public string $totalFees,
		public string $totalTaxes,
	) {
	}

	public static function fromDto(TaxReportDto $report, Portfolio $portfolio): self
	{
		$realizedTransactions = array_map(
			fn (TaxReportRealizedGainTransactionDto $tx): McpTaxRealizedTransactionDto => McpTaxRealizedTransactionDto::fromDto($tx),
			$report->realizedGains->transactions,
		);

		$dividendsByCountry = array_map(
			fn (TaxReportDividendsByCountryDto $countryData): McpTaxDividendCountryDto => McpTaxDividendCountryDto::fromDto($countryData),
			$report->dividends->dividendsByCountry,
		);

		return new self(
			year: $report->year,
			currency: $portfolio->currency->code,
			realizedGains: [
				'costBasisMethod' => $report->realizedGains->method->value,
				'totalGains' => (string) $report->realizedGains->totalGains,
				'totalLosses' => (string) $report->realizedGains->totalLosses,
				'netRealizedGainLoss' => (string) $report->realizedGains->netRealizedGainLoss,
				'totalSalesProceeds' => (string) $report->realizedGains->totalSalesProceeds,
				'totalCostBasis' => (string) $report->realizedGains->totalCostBasis,
				'totalFees' => (string) $report->realizedGains->totalFees,
				'transactions' => $realizedTransactions,
			],
			dividends: [
				'totalGross' => (string) $report->dividends->totalGross,
				'totalTax' => (string) $report->dividends->totalTax,
				'totalNet' => (string) $report->dividends->totalNet,
				'byCountry' => $dividendsByCountry,
			],
			totalFees: (string) $report->totalFees,
			totalTaxes: (string) $report->totalTaxes,
		);
	}
}
