<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerFundamental;
use FinGather\Model\Repository\TickerFundamentalRepository;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;

class TickerFundamentalProvider
{
	public function __construct(
		private readonly TickerFundamentalRepository $tickerFundamentalRepository,
		private readonly TwelveData $twelveData,
	) {
	}

	public function getTickerFundamental(Ticker $ticker): ?TickerFundamental
	{
		return $this->tickerFundamentalRepository->findTickerFundamental($ticker->id);
	}

	public function createTickerFundamental(Ticker $ticker): void
	{
		try {
			$statistics = $this->twelveData->getFundamentals()->statistics(symbol: $ticker->ticker, micCode: $ticker->market->mic);
		} catch (NotFoundException) {
			return;
		}

		$tickerFundamental = new TickerFundamental(
			ticker: $ticker,
			marketCapitalization: $statistics->statistics->valuationsMetrics->marketCapitalization,
			enterpriseValue: $statistics->statistics->valuationsMetrics->enterpriseValue,
			trailingPe: $statistics->statistics->valuationsMetrics->trailingPe,
			forwardPe: $statistics->statistics->valuationsMetrics->forwardPe,
			pegRatio: $statistics->statistics->valuationsMetrics->pegRatio,
			priceToSalesTtm: $statistics->statistics->valuationsMetrics->priceToSalesTtm,
			priceToBookMrq: $statistics->statistics->valuationsMetrics->priceToBookMrq,
			enterpriseToRevenue: $statistics->statistics->valuationsMetrics->enterpriseToRevenue,
			enterpriseToEbitda: $statistics->statistics->valuationsMetrics->enterpriseToEbitda,
			fiscalYearEnds: $statistics->statistics->financials->fiscalYearEnds,
			mostRecentQuarter: $statistics->statistics->financials->mostRecentQuarter,
			profitMargin: $statistics->statistics->financials->profitMargin,
			operatingMargin: $statistics->statistics->financials->operatingMargin,
			returnOnAssetsTtm: $statistics->statistics->financials->returnOnAssetsTtm,
			returnOnEquityTtm: $statistics->statistics->financials->returnOnEquityTtm,
			revenueTtm: $statistics->statistics->financials->incomeStatement->revenueTtm,
			revenuePerShareTtm: $statistics->statistics->financials->incomeStatement->revenuePerShareTtm,
			quarterlyRevenueGrowth: $statistics->statistics->financials->incomeStatement->quarterlyRevenueGrowth,
			grossProfitTtm: $statistics->statistics->financials->incomeStatement->grossProfitTtm,
			ebitda: $statistics->statistics->financials->incomeStatement->ebitda,
			netIncomeToCommonTtm: $statistics->statistics->financials->incomeStatement->netIncomeToCommonTtm,
			dilutedEpsTtm: $statistics->statistics->financials->incomeStatement->dilutedEpsTtm,
			quarterlyEarningsGrowthYoy: $statistics->statistics->financials->incomeStatement->quarterlyEarningsGrowthYoy,
			totalCashMrq: $statistics->statistics->financials->balanceSheet->totalCashMrq,
			totalCashPerShareMrq: $statistics->statistics->financials->balanceSheet->totalCashPerShareMrq,
			totalDebtMrq: $statistics->statistics->financials->balanceSheet->totalDebtMrq,
			totalDebtToEquityMrq: $statistics->statistics->financials->balanceSheet->totalDebtToEquityMrq,
			currentRatioMrq: $statistics->statistics->financials->balanceSheet->currentRatioMrq,
			bookValuePerShareMrq: $statistics->statistics->financials->balanceSheet->bookValuePerShareMrq,
			operatingCashFlowTtm: $statistics->statistics->financials->cashFlow->operatingCashFlowTtm,
			leveredFreeCashFlowTtm: $statistics->statistics->financials->cashFlow->leveredFreeCashFlowTtm,
			sharesOutstanding: $statistics->statistics->stockStatistics->sharesOutstanding,
			floatShares: $statistics->statistics->stockStatistics->floatShares,
			avg10Volume: $statistics->statistics->stockStatistics->avg10Volume,
			avg90Volume: $statistics->statistics->stockStatistics->avg90Volume,
			sharesShort: $statistics->statistics->stockStatistics->sharesShort,
			shortRatio: $statistics->statistics->stockStatistics->shortRatio,
			shortPercentOfSharesOutstanding: $statistics->statistics->stockStatistics->shortPercentOfSharesOutstanding,
			percentHeldByInsiders: $statistics->statistics->stockStatistics->percentHeldByInsiders,
			percentHeldByInstitutions: $statistics->statistics->stockStatistics->percentHeldByInstitutions,
			fiftyTwoWeekLow: $statistics->statistics->stockPriceSummary->fiftyTwoWeekLow,
			fiftyTwoWeekHigh: $statistics->statistics->stockPriceSummary->fiftyTwoWeekHigh,
			fiftyTwoWeekChange: $statistics->statistics->stockPriceSummary->fiftyTwoWeekChange,
			beta: $statistics->statistics->stockPriceSummary->beta,
			day50Ma: $statistics->statistics->stockPriceSummary->day50Ma,
			day200Ma: $statistics->statistics->stockPriceSummary->day200Ma,
			forwardAnnualDividendRate: $statistics->statistics->dividendsAndSplits->forwardAnnualDividendRate,
			forwardAnnualDividendYield: $statistics->statistics->dividendsAndSplits->forwardAnnualDividendYield,
			trailingAnnualDividendRate: $statistics->statistics->dividendsAndSplits->trailingAnnualDividendRate,
			trailingAnnualDividendYield: $statistics->statistics->dividendsAndSplits->trailingAnnualDividendYield,
			fiveYearAverageDividendYield: $statistics->statistics->dividendsAndSplits->fiveYearAverageDividendYield,
			payoutRatio: $statistics->statistics->dividendsAndSplits->payoutRatio,
			dividendDate: $statistics->statistics->dividendsAndSplits->dividendDate,
			exDividendDate: $statistics->statistics->dividendsAndSplits->exDividendDate,
		);

		$this->tickerFundamentalRepository->persist($tickerFundamental);
	}

	public function updateTickerFundamental(TickerFundamental $tickerFundamental): TickerFundamental
	{
		$ticker = $tickerFundamental->ticker;

		try {
			$statistics = $this->twelveData->getFundamentals()->statistics(symbol: $ticker->ticker, micCode: $ticker->market->mic);
		} catch (NotFoundException) {
			return $tickerFundamental;
		}

		$tickerFundamental->marketCapitalization = $statistics->statistics->valuationsMetrics->marketCapitalization;
		$tickerFundamental->enterpriseValue = $statistics->statistics->valuationsMetrics->enterpriseValue;
		$tickerFundamental->trailingPe = $statistics->statistics->valuationsMetrics->trailingPe;
		$tickerFundamental->forwardPe = $statistics->statistics->valuationsMetrics->forwardPe;
		$tickerFundamental->pegRatio = $statistics->statistics->valuationsMetrics->pegRatio;
		$tickerFundamental->priceToSalesTtm = $statistics->statistics->valuationsMetrics->priceToSalesTtm;
		$tickerFundamental->priceToBookMrq = $statistics->statistics->valuationsMetrics->priceToBookMrq;
		$tickerFundamental->enterpriseToRevenue = $statistics->statistics->valuationsMetrics->enterpriseToRevenue;
		$tickerFundamental->enterpriseToEbitda = $statistics->statistics->valuationsMetrics->enterpriseToEbitda;
		$tickerFundamental->fiscalYearEnds = $statistics->statistics->financials->fiscalYearEnds;
		$tickerFundamental->mostRecentQuarter = $statistics->statistics->financials->mostRecentQuarter;
		$tickerFundamental->profitMargin = $statistics->statistics->financials->profitMargin;
		$tickerFundamental->operatingMargin = $statistics->statistics->financials->operatingMargin;
		$tickerFundamental->returnOnAssetsTtm = $statistics->statistics->financials->returnOnAssetsTtm;
		$tickerFundamental->returnOnEquityTtm = $statistics->statistics->financials->returnOnEquityTtm;
		$tickerFundamental->revenueTtm = $statistics->statistics->financials->incomeStatement->revenueTtm;
		$tickerFundamental->revenuePerShareTtm = $statistics->statistics->financials->incomeStatement->revenuePerShareTtm;
		$tickerFundamental->quarterlyRevenueGrowth = $statistics->statistics->financials->incomeStatement->quarterlyRevenueGrowth;
		$tickerFundamental->grossProfitTtm = $statistics->statistics->financials->incomeStatement->grossProfitTtm;
		$tickerFundamental->ebitda = $statistics->statistics->financials->incomeStatement->ebitda;
		$tickerFundamental->netIncomeToCommonTtm = $statistics->statistics->financials->incomeStatement->netIncomeToCommonTtm;
		$tickerFundamental->dilutedEpsTtm = $statistics->statistics->financials->incomeStatement->dilutedEpsTtm;
		$tickerFundamental->quarterlyEarningsGrowthYoy = $statistics->statistics->financials->incomeStatement->quarterlyEarningsGrowthYoy;
		$tickerFundamental->totalCashMrq = $statistics->statistics->financials->balanceSheet->totalCashMrq;
		$tickerFundamental->totalCashPerShareMrq = $statistics->statistics->financials->balanceSheet->totalCashPerShareMrq;
		$tickerFundamental->totalDebtMrq = $statistics->statistics->financials->balanceSheet->totalDebtMrq;
		$tickerFundamental->totalDebtToEquityMrq = $statistics->statistics->financials->balanceSheet->totalDebtToEquityMrq;
		$tickerFundamental->currentRatioMrq = $statistics->statistics->financials->balanceSheet->currentRatioMrq;
		$tickerFundamental->bookValuePerShareMrq = $statistics->statistics->financials->balanceSheet->bookValuePerShareMrq;
		$tickerFundamental->operatingCashFlowTtm = $statistics->statistics->financials->cashFlow->operatingCashFlowTtm;
		$tickerFundamental->leveredFreeCashFlowTtm = $statistics->statistics->financials->cashFlow->leveredFreeCashFlowTtm;
		$tickerFundamental->sharesOutstanding = $statistics->statistics->stockStatistics->sharesOutstanding;
		$tickerFundamental->floatShares = $statistics->statistics->stockStatistics->floatShares;
		$tickerFundamental->avg10Volume = $statistics->statistics->stockStatistics->avg10Volume;
		$tickerFundamental->avg90Volume = $statistics->statistics->stockStatistics->avg90Volume;
		$tickerFundamental->sharesShort = $statistics->statistics->stockStatistics->sharesShort;
		$tickerFundamental->shortRatio = $statistics->statistics->stockStatistics->shortRatio;
		$tickerFundamental->shortPercentOfSharesOutstanding = $statistics->statistics->stockStatistics->shortPercentOfSharesOutstanding;
		$tickerFundamental->percentHeldByInsiders = $statistics->statistics->stockStatistics->percentHeldByInsiders;
		$tickerFundamental->percentHeldByInstitutions = $statistics->statistics->stockStatistics->percentHeldByInstitutions;
		$tickerFundamental->fiftyTwoWeekLow = $statistics->statistics->stockPriceSummary->fiftyTwoWeekLow;
		$tickerFundamental->fiftyTwoWeekHigh = $statistics->statistics->stockPriceSummary->fiftyTwoWeekHigh;
		$tickerFundamental->fiftyTwoWeekChange = $statistics->statistics->stockPriceSummary->fiftyTwoWeekChange;
		$tickerFundamental->beta = $statistics->statistics->stockPriceSummary->beta;
		$tickerFundamental->day50Ma = $statistics->statistics->stockPriceSummary->day50Ma;
		$tickerFundamental->day200Ma = $statistics->statistics->stockPriceSummary->day200Ma;
		$tickerFundamental->forwardAnnualDividendRate = $statistics->statistics->dividendsAndSplits->forwardAnnualDividendRate;
		$tickerFundamental->forwardAnnualDividendYield = $statistics->statistics->dividendsAndSplits->forwardAnnualDividendYield;
		$tickerFundamental->trailingAnnualDividendRate = $statistics->statistics->dividendsAndSplits->trailingAnnualDividendRate;
		$tickerFundamental->trailingAnnualDividendYield = $statistics->statistics->dividendsAndSplits->trailingAnnualDividendYield;
		$tickerFundamental->fiveYearAverageDividendYield = $statistics->statistics->dividendsAndSplits->fiveYearAverageDividendYield;
		$tickerFundamental->payoutRatio = $statistics->statistics->dividendsAndSplits->payoutRatio;
		$tickerFundamental->dividendDate = $statistics->statistics->dividendsAndSplits->dividendDate;
		$tickerFundamental->exDividendDate = $statistics->statistics->dividendsAndSplits->exDividendDate;

		$this->tickerFundamentalRepository->persist($tickerFundamental);

		return $tickerFundamental;
	}
}
