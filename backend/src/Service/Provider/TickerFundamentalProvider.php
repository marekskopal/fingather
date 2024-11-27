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
			$statistics = $this->twelveData->getFundamentals()->statistics(
				symbol: $ticker->getTicker(),
				micCode: $ticker->getMarket()->getMic(),
			);
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
		$ticker = $tickerFundamental->getTicker();

		try {
			$statistics = $this->twelveData->getFundamentals()->statistics(
				symbol: $ticker->getTicker(),
				micCode: $ticker->getMarket()->getMic(),
			);
		} catch (NotFoundException) {
			return $tickerFundamental;
		}

		$tickerFundamental->setMarketCapitalization($statistics->statistics->valuationsMetrics->marketCapitalization);
		$tickerFundamental->setEnterpriseValue($statistics->statistics->valuationsMetrics->enterpriseValue);
		$tickerFundamental->setTrailingPe($statistics->statistics->valuationsMetrics->trailingPe);
		$tickerFundamental->setForwardPe($statistics->statistics->valuationsMetrics->forwardPe);
		$tickerFundamental->setPegRatio($statistics->statistics->valuationsMetrics->pegRatio);
		$tickerFundamental->setPriceToSalesTtm($statistics->statistics->valuationsMetrics->priceToSalesTtm);
		$tickerFundamental->setPriceToBookMrq($statistics->statistics->valuationsMetrics->priceToBookMrq);
		$tickerFundamental->setEnterpriseToRevenue($statistics->statistics->valuationsMetrics->enterpriseToRevenue);
		$tickerFundamental->setEnterpriseToEbitda($statistics->statistics->valuationsMetrics->enterpriseToEbitda);
		$tickerFundamental->setFiscalYearEnds($statistics->statistics->financials->fiscalYearEnds);
		$tickerFundamental->setMostRecentQuarter($statistics->statistics->financials->mostRecentQuarter);
		$tickerFundamental->setProfitMargin($statistics->statistics->financials->profitMargin);
		$tickerFundamental->setOperatingMargin($statistics->statistics->financials->operatingMargin);
		$tickerFundamental->setReturnOnAssetsTtm($statistics->statistics->financials->returnOnAssetsTtm);
		$tickerFundamental->setReturnOnEquityTtm($statistics->statistics->financials->returnOnEquityTtm);
		$tickerFundamental->setRevenueTtm($statistics->statistics->financials->incomeStatement->revenueTtm);
		$tickerFundamental->setRevenuePerShareTtm($statistics->statistics->financials->incomeStatement->revenuePerShareTtm);
		$tickerFundamental->setQuarterlyRevenueGrowth($statistics->statistics->financials->incomeStatement->quarterlyRevenueGrowth);
		$tickerFundamental->setGrossProfitTtm($statistics->statistics->financials->incomeStatement->grossProfitTtm);
		$tickerFundamental->setEbitda($statistics->statistics->financials->incomeStatement->ebitda);
		$tickerFundamental->setNetIncomeToCommonTtm($statistics->statistics->financials->incomeStatement->netIncomeToCommonTtm);
		$tickerFundamental->setDilutedEpsTtm($statistics->statistics->financials->incomeStatement->dilutedEpsTtm);
		$tickerFundamental->setQuarterlyEarningsGrowthYoy($statistics->statistics->financials->incomeStatement->quarterlyEarningsGrowthYoy);
		$tickerFundamental->setTotalCashMrq($statistics->statistics->financials->balanceSheet->totalCashMrq);
		$tickerFundamental->setTotalCashPerShareMrq($statistics->statistics->financials->balanceSheet->totalCashPerShareMrq);
		$tickerFundamental->setTotalDebtMrq($statistics->statistics->financials->balanceSheet->totalDebtMrq);
		$tickerFundamental->setTotalDebtToEquityMrq($statistics->statistics->financials->balanceSheet->totalDebtToEquityMrq);
		$tickerFundamental->setCurrentRatioMrq($statistics->statistics->financials->balanceSheet->currentRatioMrq);
		$tickerFundamental->setBookValuePerShareMrq($statistics->statistics->financials->balanceSheet->bookValuePerShareMrq);
		$tickerFundamental->setOperatingCashFlowTtm($statistics->statistics->financials->cashFlow->operatingCashFlowTtm);
		$tickerFundamental->setLeveredFreeCashFlowTtm($statistics->statistics->financials->cashFlow->leveredFreeCashFlowTtm);
		$tickerFundamental->setSharesOutstanding($statistics->statistics->stockStatistics->sharesOutstanding);
		$tickerFundamental->setFloatShares($statistics->statistics->stockStatistics->floatShares);
		$tickerFundamental->setAvg10Volume($statistics->statistics->stockStatistics->avg10Volume);
		$tickerFundamental->setAvg90Volume($statistics->statistics->stockStatistics->avg90Volume);
		$tickerFundamental->setSharesShort($statistics->statistics->stockStatistics->sharesShort);
		$tickerFundamental->setShortRatio($statistics->statistics->stockStatistics->shortRatio);
		$tickerFundamental->setShortPercentOfSharesOutstanding($statistics->statistics->stockStatistics->shortPercentOfSharesOutstanding);
		$tickerFundamental->setPercentHeldByInsiders($statistics->statistics->stockStatistics->percentHeldByInsiders);
		$tickerFundamental->setPercentHeldByInstitutions($statistics->statistics->stockStatistics->percentHeldByInstitutions);
		$tickerFundamental->setFiftyTwoWeekLow($statistics->statistics->stockPriceSummary->fiftyTwoWeekLow);
		$tickerFundamental->setFiftyTwoWeekHigh($statistics->statistics->stockPriceSummary->fiftyTwoWeekHigh);
		$tickerFundamental->setFiftyTwoWeekChange($statistics->statistics->stockPriceSummary->fiftyTwoWeekChange);
		$tickerFundamental->setBeta($statistics->statistics->stockPriceSummary->beta);
		$tickerFundamental->setDay50Ma($statistics->statistics->stockPriceSummary->day50Ma);
		$tickerFundamental->setDay200Ma($statistics->statistics->stockPriceSummary->day200Ma);
		$tickerFundamental->setForwardAnnualDividendRate($statistics->statistics->dividendsAndSplits->forwardAnnualDividendRate);
		$tickerFundamental->setForwardAnnualDividendYield($statistics->statistics->dividendsAndSplits->forwardAnnualDividendYield);
		$tickerFundamental->setTrailingAnnualDividendRate($statistics->statistics->dividendsAndSplits->trailingAnnualDividendRate);
		$tickerFundamental->setTrailingAnnualDividendYield($statistics->statistics->dividendsAndSplits->trailingAnnualDividendYield);
		$tickerFundamental->setFiveYearAverageDividendYield($statistics->statistics->dividendsAndSplits->fiveYearAverageDividendYield);
		$tickerFundamental->setPayoutRatio($statistics->statistics->dividendsAndSplits->payoutRatio);
		$tickerFundamental->setDividendDate($statistics->statistics->dividendsAndSplits->dividendDate);
		$tickerFundamental->setExDividendDate($statistics->statistics->dividendsAndSplits->exDividendDate);

		$this->tickerFundamentalRepository->persist($tickerFundamental);

		return $tickerFundamental;
	}
}
