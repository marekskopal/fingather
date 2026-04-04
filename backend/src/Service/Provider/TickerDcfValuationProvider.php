<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerDcfHistoryPoint;
use FinGather\Model\Repository\TickerDcfHistoryPointRepository;
use FinGather\Service\DataCalculator\Dcf\DcfCalculationException;
use FinGather\Service\DataCalculator\Dcf\DcfCalculatorInterface;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfAssumptions;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfHistoryPointDto;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfInputs;
use FinGather\Service\Provider\Dto\DcfValuationChipDto;
use FinGather\Service\Provider\Dto\DcfValuationView;
use MarekSkopal\TwelveData\Dto\Fundamentals\CashFlowCashFlow;
use MarekSkopal\TwelveData\Dto\Fundamentals\IncomeStatementIncomeStatement;
use MarekSkopal\TwelveData\Enum\PeriodEnum;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;

final readonly class TickerDcfValuationProvider implements TickerDcfValuationProviderInterface
{
	private const int HistoryOutputSize = 5;

	public function __construct(
		private TickerDcfHistoryPointRepository $tickerDcfHistoryPointRepository,
		private TickerFundamentalProviderInterface $tickerFundamentalProvider,
		private TickerDataProviderInterface $tickerDataProvider,
		private DcfCalculatorInterface $dcfCalculator,
		private TwelveData $twelveData,
	) {
	}

	public function getDcfValuationView(Ticker $ticker, ?DcfAssumptions $assumptions = null): ?DcfValuationView
	{
		$history = $this->loadHistory($ticker);
		if (count($history) === 0) {
			return null;
		}

		$fundamental = $this->tickerFundamentalProvider->getTickerFundamental($ticker);
		if ($fundamental === null || $fundamental->sharesOutstanding === null || $fundamental->sharesOutstanding <= 0) {
			return null;
		}

		$currentPrice = $this->tickerDataProvider->getLastTickerDataClose($ticker, new DateTimeImmutable());

		$inputs = new DcfInputs(
			sharesOutstanding: $fundamental->sharesOutstanding,
			latestRevenue: $fundamental->revenueTtm,
			latestFcfe: $fundamental->leveredFreeCashFlowTtm,
			quarterlyRevenueGrowth: $fundamental->quarterlyRevenueGrowth,
			beta: $fundamental->beta,
			history: $history,
			currentPrice: $currentPrice,
		);

		$result = $this->dcfCalculator->calculate($inputs, $assumptions ?? DcfAssumptions::default());

		return new DcfValuationView(ticker: $ticker, inputs: $inputs, result: $result);
	}

	public function getDcfValuationChip(Ticker $ticker): DcfValuationChipDto
	{
		try {
			$view = $this->getDcfValuationView($ticker);
		} catch (DcfCalculationException) {
			return DcfValuationChipDto::empty();
		}

		if ($view === null) {
			return DcfValuationChipDto::empty();
		}

		return new DcfValuationChipDto(
			diffPercent: $view->result->valuationDiffPercent,
			status: $view->result->valuationStatus,
		);
	}

	public function createOrUpdateTickerDcfValuation(Ticker $ticker): void
	{
		$cashFlows = $this->fetchCashFlows($ticker);
		$incomeStatements = $this->fetchIncomeStatements($ticker);

		if (count($cashFlows) === 0 && count($incomeStatements) === 0) {
			return;
		}

		$revenueByDate = [];
		foreach ($incomeStatements as $statement) {
			$revenueByDate[$statement->fiscalDate->format('Y-m-d')] = $statement->sales;
		}

		$pointsData = [];
		foreach ($cashFlows as $cashFlow) {
			$dateKey = $cashFlow->fiscalDate;
			$pointsData[$dateKey] = [
				'freeCashFlow' => $this->extractFreeCashFlow($cashFlow),
				'revenue' => $revenueByDate[$dateKey] ?? null,
			];
		}

		foreach ($revenueByDate as $dateKey => $revenue) {
			if (isset($pointsData[$dateKey])) {
				continue;
			}

			$pointsData[$dateKey] = [
				'freeCashFlow' => null,
				'revenue' => $revenue,
			];
		}

		$this->tickerDcfHistoryPointRepository->deleteByTicker($ticker->id);

		foreach ($pointsData as $dateKey => $data) {
			$historyPoint = new TickerDcfHistoryPoint(
				ticker: $ticker,
				fiscalDate: new DateTimeImmutable($dateKey),
				freeCashFlow: $data['freeCashFlow'],
				revenue: $data['revenue'],
			);
			$this->tickerDcfHistoryPointRepository->persist($historyPoint);
		}
	}

	/** @return list<DcfHistoryPointDto> */
	private function loadHistory(Ticker $ticker): array
	{
		$history = [];
		foreach ($this->tickerDcfHistoryPointRepository->findByTicker($ticker->id) as $point) {
			$history[] = new DcfHistoryPointDto(
				fiscalDate: $point->fiscalDate,
				freeCashFlow: $point->freeCashFlow,
				revenue: $point->revenue,
			);
		}

		usort(
			$history,
			static fn (DcfHistoryPointDto $a, DcfHistoryPointDto $b): int => $b->fiscalDate <=> $a->fiscalDate,
		);

		return $history;
	}

	/** @return list<CashFlowCashFlow> */
	private function fetchCashFlows(Ticker $ticker): array
	{
		try {
			$response = $this->twelveData->fundamentals->cashFlow(
				symbol: $ticker->ticker,
				micCode: $ticker->market->mic,
				period: PeriodEnum::Annual,
				outputsize: self::HistoryOutputSize,
			);
		} catch (NotFoundException) {
			return [];
		}

		return $response->cashFlow;
	}

	/** @return list<IncomeStatementIncomeStatement> */
	private function fetchIncomeStatements(Ticker $ticker): array
	{
		try {
			$response = $this->twelveData->fundamentals->incomeStatement(
				symbol: $ticker->ticker,
				micCode: $ticker->market->mic,
				period: PeriodEnum::Annual,
				outputsize: self::HistoryOutputSize,
			);
		} catch (NotFoundException) {
			return [];
		}

		return $response->incomeStatement;
	}

	private function extractFreeCashFlow(CashFlowCashFlow $cashFlow): ?int
	{
		if ($cashFlow->freeCashFlow !== null) {
			return $cashFlow->freeCashFlow;
		}

		$operatingCashFlow = $cashFlow->operatingActivities->operatingCashFlow;
		$capitalExpenditures = $cashFlow->investingActivities->capitalExpenditures;
		if ($operatingCashFlow === null || $capitalExpenditures === null) {
			return null;
		}

		return $operatingCashFlow - abs($capitalExpenditures);
	}
}
