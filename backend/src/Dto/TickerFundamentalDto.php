<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\TickerFundamental;
use FinGather\Utils\DateTimeUtils;

final readonly class TickerFundamentalDto
{
	public function __construct(
		public int $id,
		public int $tickerId,
		public ?int $marketCapitalization,
		public ?int $enterpriseValue,
		public ?float $trailingPe,
		public ?float $forwardPe,
		public ?float $pegRatio,
		public ?float $priceToSalesTtm,
		public ?float $priceToBookMrq,
		public ?float $enterpriseToRevenue,
		public ?float $enterpriseToEbitda,
		public ?string $fiscalYearEnds,
		public ?string $mostRecentQuarter,
		public ?float $profitMargin,
		public ?float $operatingMargin,
		public ?float $returnOnAssetsTtm,
		public ?float $returnOnEquityTtm,
		public ?int $revenueTtm,
		public ?float $revenuePerShareTtm,
		public ?float $quarterlyRevenueGrowth,
		public ?int $grossProfitTtm,
		public ?int $ebitda,
		public ?int $netIncomeToCommonTtm,
		public ?float $dilutedEpsTtm,
		public ?float $quarterlyEarningsGrowthYoy,
		public ?int $totalCashMrq,
		public ?float $totalCashPerShareMrq,
		public ?int $totalDebtMrq,
		public ?float $totalDebtToEquityMrq,
		public ?float $currentRatioMrq,
		public ?float $bookValuePerShareMrq,
		public ?int $operatingCashFlowTtm,
		public ?int $leveredFreeCashFlowTtm,
		public ?int $sharesOutstanding,
		public ?int $floatShares,
		public ?int $avg10Volume,
		public ?int $avg90Volume,
		public ?int $sharesShort,
		public ?float $shortRatio,
		public ?float $shortPercentOfSharesOutstanding,
		public ?float $percentHeldByInsiders,
		public ?float $percentHeldByInstitutions,
		public ?float $fiftyTwoWeekLow,
		public ?float $fiftyTwoWeekHigh,
		public ?float $fiftyTwoWeekChange,
		public ?float $beta,
		public ?float $day50Ma,
		public ?float $day200Ma,
		public ?float $forwardAnnualDividendRate,
		public ?float $forwardAnnualDividendYield,
		public ?float $trailingAnnualDividendRate,
		public ?float $trailingAnnualDividendYield,
		public ?float $fiveYearAverageDividendYield,
		public ?float $payoutRatio,
		public ?string $dividendDate,
		public ?string $exDividendDate,
	) {
	}

	public static function fromEntity(TickerFundamental $tickerFundamental): self
	{
		return new self(
			id: $tickerFundamental->id,
			tickerId: $tickerFundamental->ticker->id,
			marketCapitalization: $tickerFundamental->marketCapitalization,
			enterpriseValue: $tickerFundamental->enterpriseValue,
			trailingPe: $tickerFundamental->trailingPe,
			forwardPe: $tickerFundamental->forwardPe,
			pegRatio: $tickerFundamental->pegRatio,
			priceToSalesTtm: $tickerFundamental->priceToSalesTtm,
			priceToBookMrq: $tickerFundamental->priceToBookMrq,
			enterpriseToRevenue: $tickerFundamental->enterpriseToRevenue,
			enterpriseToEbitda: $tickerFundamental->enterpriseToEbitda,
			fiscalYearEnds: $tickerFundamental->fiscalYearEnds !== null ? DateTimeUtils::formatZulu(
				$tickerFundamental->fiscalYearEnds,
			) : null,
			mostRecentQuarter: $tickerFundamental->mostRecentQuarter !== null ? DateTimeUtils::formatZulu(
				$tickerFundamental->mostRecentQuarter,
			) : null,
			profitMargin: $tickerFundamental->profitMargin,
			operatingMargin: $tickerFundamental->operatingMargin,
			returnOnAssetsTtm: $tickerFundamental->returnOnAssetsTtm,
			returnOnEquityTtm: $tickerFundamental->returnOnEquityTtm,
			revenueTtm: $tickerFundamental->revenueTtm,
			revenuePerShareTtm: $tickerFundamental->revenuePerShareTtm,
			quarterlyRevenueGrowth: $tickerFundamental->quarterlyRevenueGrowth,
			grossProfitTtm: $tickerFundamental->grossProfitTtm,
			ebitda: $tickerFundamental->ebitda,
			netIncomeToCommonTtm: $tickerFundamental->netIncomeToCommonTtm,
			dilutedEpsTtm: $tickerFundamental->dilutedEpsTtm,
			quarterlyEarningsGrowthYoy: $tickerFundamental->quarterlyEarningsGrowthYoy,
			totalCashMrq: $tickerFundamental->totalCashMrq,
			totalCashPerShareMrq: $tickerFundamental->totalCashPerShareMrq,
			totalDebtMrq: $tickerFundamental->totalDebtMrq,
			totalDebtToEquityMrq: $tickerFundamental->totalDebtToEquityMrq,
			currentRatioMrq: $tickerFundamental->currentRatioMrq,
			bookValuePerShareMrq: $tickerFundamental->bookValuePerShareMrq,
			operatingCashFlowTtm: $tickerFundamental->operatingCashFlowTtm,
			leveredFreeCashFlowTtm: $tickerFundamental->leveredFreeCashFlowTtm,
			sharesOutstanding: $tickerFundamental->sharesOutstanding,
			floatShares: $tickerFundamental->floatShares,
			avg10Volume: $tickerFundamental->avg10Volume,
			avg90Volume: $tickerFundamental->avg90Volume,
			sharesShort: $tickerFundamental->sharesShort,
			shortRatio: $tickerFundamental->shortRatio,
			shortPercentOfSharesOutstanding: $tickerFundamental->shortPercentOfSharesOutstanding,
			percentHeldByInsiders: $tickerFundamental->percentHeldByInsiders,
			percentHeldByInstitutions: $tickerFundamental->percentHeldByInstitutions,
			fiftyTwoWeekLow: $tickerFundamental->fiftyTwoWeekLow,
			fiftyTwoWeekHigh: $tickerFundamental->fiftyTwoWeekHigh,
			fiftyTwoWeekChange: $tickerFundamental->fiftyTwoWeekChange,
			beta: $tickerFundamental->beta,
			day50Ma: $tickerFundamental->day50Ma,
			day200Ma: $tickerFundamental->day200Ma,
			forwardAnnualDividendRate: $tickerFundamental->forwardAnnualDividendRate,
			forwardAnnualDividendYield: $tickerFundamental->forwardAnnualDividendYield,
			trailingAnnualDividendRate: $tickerFundamental->trailingAnnualDividendRate,
			trailingAnnualDividendYield: $tickerFundamental->trailingAnnualDividendYield,
			fiveYearAverageDividendYield: $tickerFundamental->fiveYearAverageDividendYield,
			payoutRatio: $tickerFundamental->payoutRatio,
			dividendDate: $tickerFundamental->dividendDate !== null ? DateTimeUtils::formatZulu($tickerFundamental->dividendDate) : null,
			exDividendDate:$tickerFundamental->exDividendDate !== null ? DateTimeUtils::formatZulu(
				$tickerFundamental->exDividendDate,
			) : null,
		);
	}
}
