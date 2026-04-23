<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerFundamental;
use FinGather\Utils\DateTimeUtils;

final readonly class McpTickerFundamentalsDto
{
	public function __construct(
		public int $tickerId,
		public string $ticker,
		public string $name,
		// Valuation
		public ?int $marketCapitalization,
		public ?int $enterpriseValue,
		public ?float $trailingPe,
		public ?float $forwardPe,
		public ?float $pegRatio,
		public ?float $priceToSalesTtm,
		public ?float $priceToBookMrq,
		public ?float $enterpriseToRevenue,
		public ?float $enterpriseToEbitda,
		// Profitability
		public ?float $profitMargin,
		public ?float $operatingMargin,
		public ?float $returnOnAssetsTtm,
		public ?float $returnOnEquityTtm,
		// Growth
		public ?float $quarterlyRevenueGrowth,
		public ?float $quarterlyEarningsGrowthYoy,
		// Income Statement
		public ?int $revenueTtm,
		public ?float $revenuePerShareTtm,
		public ?int $grossProfitTtm,
		public ?int $ebitda,
		public ?int $netIncomeToCommonTtm,
		public ?float $dilutedEpsTtm,
		// Balance Sheet
		public ?int $totalCashMrq,
		public ?float $totalCashPerShareMrq,
		public ?int $totalDebtMrq,
		public ?float $totalDebtToEquityMrq,
		public ?float $currentRatioMrq,
		public ?float $bookValuePerShareMrq,
		// Cash Flow
		public ?int $operatingCashFlowTtm,
		public ?int $leveredFreeCashFlowTtm,
		// Dividends
		public ?float $forwardAnnualDividendRate,
		public ?float $forwardAnnualDividendYield,
		public ?float $trailingAnnualDividendRate,
		public ?float $trailingAnnualDividendYield,
		public ?float $fiveYearAverageDividendYield,
		public ?float $payoutRatio,
		public ?string $dividendDate,
		public ?string $exDividendDate,
		// Trading Info
		public ?float $fiftyTwoWeekLow,
		public ?float $fiftyTwoWeekHigh,
		public ?float $fiftyTwoWeekChange,
		public ?float $beta,
		public ?float $day50Ma,
		public ?float $day200Ma,
		// Share Stats
		public ?int $sharesOutstanding,
		public ?int $floatShares,
		public ?int $avg10Volume,
		public ?int $avg90Volume,
		public ?int $sharesShort,
		public ?float $shortRatio,
		public ?float $shortPercentOfSharesOutstanding,
		public ?float $percentHeldByInsiders,
		public ?float $percentHeldByInstitutions,
	) {
	}

	public static function fromEntity(Ticker $ticker, TickerFundamental $fundamental): self
	{
		return new self(
			tickerId: $ticker->id,
			ticker: $ticker->ticker,
			name: $ticker->name,
			marketCapitalization: $fundamental->marketCapitalization,
			enterpriseValue: $fundamental->enterpriseValue,
			trailingPe: $fundamental->trailingPe,
			forwardPe: $fundamental->forwardPe,
			pegRatio: $fundamental->pegRatio,
			priceToSalesTtm: $fundamental->priceToSalesTtm,
			priceToBookMrq: $fundamental->priceToBookMrq,
			enterpriseToRevenue: $fundamental->enterpriseToRevenue,
			enterpriseToEbitda: $fundamental->enterpriseToEbitda,
			profitMargin: $fundamental->profitMargin,
			operatingMargin: $fundamental->operatingMargin,
			returnOnAssetsTtm: $fundamental->returnOnAssetsTtm,
			returnOnEquityTtm: $fundamental->returnOnEquityTtm,
			quarterlyRevenueGrowth: $fundamental->quarterlyRevenueGrowth,
			quarterlyEarningsGrowthYoy: $fundamental->quarterlyEarningsGrowthYoy,
			revenueTtm: $fundamental->revenueTtm,
			revenuePerShareTtm: $fundamental->revenuePerShareTtm,
			grossProfitTtm: $fundamental->grossProfitTtm,
			ebitda: $fundamental->ebitda,
			netIncomeToCommonTtm: $fundamental->netIncomeToCommonTtm,
			dilutedEpsTtm: $fundamental->dilutedEpsTtm,
			totalCashMrq: $fundamental->totalCashMrq,
			totalCashPerShareMrq: $fundamental->totalCashPerShareMrq,
			totalDebtMrq: $fundamental->totalDebtMrq,
			totalDebtToEquityMrq: $fundamental->totalDebtToEquityMrq,
			currentRatioMrq: $fundamental->currentRatioMrq,
			bookValuePerShareMrq: $fundamental->bookValuePerShareMrq,
			operatingCashFlowTtm: $fundamental->operatingCashFlowTtm,
			leveredFreeCashFlowTtm: $fundamental->leveredFreeCashFlowTtm,
			forwardAnnualDividendRate: $fundamental->forwardAnnualDividendRate,
			forwardAnnualDividendYield: $fundamental->forwardAnnualDividendYield,
			trailingAnnualDividendRate: $fundamental->trailingAnnualDividendRate,
			trailingAnnualDividendYield: $fundamental->trailingAnnualDividendYield,
			fiveYearAverageDividendYield: $fundamental->fiveYearAverageDividendYield,
			payoutRatio: $fundamental->payoutRatio,
			dividendDate: $fundamental->dividendDate?->format('Y-m-d'),
			exDividendDate: $fundamental->exDividendDate?->format('Y-m-d'),
			fiftyTwoWeekLow: $fundamental->fiftyTwoWeekLow,
			fiftyTwoWeekHigh: $fundamental->fiftyTwoWeekHigh,
			fiftyTwoWeekChange: $fundamental->fiftyTwoWeekChange,
			beta: $fundamental->beta,
			day50Ma: $fundamental->day50Ma,
			day200Ma: $fundamental->day200Ma,
			sharesOutstanding: $fundamental->sharesOutstanding,
			floatShares: $fundamental->floatShares,
			avg10Volume: $fundamental->avg10Volume,
			avg90Volume: $fundamental->avg90Volume,
			sharesShort: $fundamental->sharesShort,
			shortRatio: $fundamental->shortRatio,
			shortPercentOfSharesOutstanding: $fundamental->shortPercentOfSharesOutstanding,
			percentHeldByInsiders: $fundamental->percentHeldByInsiders,
			percentHeldByInstitutions: $fundamental->percentHeldByInstitutions,
		);
	}
}
