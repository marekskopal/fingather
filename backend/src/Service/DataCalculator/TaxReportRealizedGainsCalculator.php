<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainsDto;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainTransactionDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\Provider\CurrentTransactionProviderInterface;
use FinGather\Service\Provider\Dto\SplitDto;
use FinGather\Service\Provider\SplitProviderInterface;

final class TaxReportRealizedGainsCalculator
{
	public function __construct(
		private readonly CurrentTransactionProviderInterface $currentTransactionProvider,
		private readonly SplitProviderInterface $splitProvider,
	) {
	}

	public function calculate(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $yearStart,
		DateTimeImmutable $yearEnd,
	): TaxReportRealizedGainsDto {
		$allTransactionsByAsset = $this->currentTransactionProvider->loadTransactions(user: $user, portfolio: $portfolio);

		$transactions = [];
		$totalSalesProceeds = new Decimal(0);
		$totalCostBasis = new Decimal(0);
		$totalGains = new Decimal(0);
		$totalLosses = new Decimal(0);
		$totalFees = new Decimal(0);

		foreach ($allTransactionsByAsset as $assetTransactions) {
			$this->processAssetRealizedGains(
				$assetTransactions,
				$yearStart,
				$yearEnd,
				$transactions,
				$totalSalesProceeds,
				$totalCostBasis,
				$totalGains,
				$totalLosses,
				$totalFees,
			);
		}

		return new TaxReportRealizedGainsDto(
			totalSalesProceeds: $totalSalesProceeds,
			totalCostBasis: $totalCostBasis,
			totalGains: $totalGains,
			totalLosses: $totalLosses,
			totalFees: $totalFees,
			netRealizedGainLoss: $totalGains->sub($totalLosses),
			transactions: $transactions,
		);
	}

	/**
	 * @param list<Transaction> $assetTransactions
	 * @param list<TaxReportRealizedGainTransactionDto> $transactions
	 */
	private function processAssetRealizedGains(
		array $assetTransactions,
		DateTimeImmutable $yearStart,
		DateTimeImmutable $yearEnd,
		array &$transactions,
		Decimal &$totalSalesProceeds,
		Decimal &$totalCostBasis,
		Decimal &$totalGains,
		Decimal &$totalLosses,
		Decimal &$totalFees,
	): void {
		if (count($assetTransactions) === 0) {
			return;
		}

		$ticker = $assetTransactions[0]->asset->ticker;
		$splits = $this->splitProvider->getSplits($ticker);

		/** @var array<int, TransactionBuyDto> $buys */
		$buys = [];

		foreach ($assetTransactions as $transaction) {
			if ($transaction->actionType === TransactionActionTypeEnum::Buy) {
				$this->addBuyLot($buys, $transaction, $splits, $yearEnd);
				continue;
			}

			if ($transaction->actionType !== TransactionActionTypeEnum::Sell) {
				continue;
			}

			if ($transaction->actionCreated < $yearStart || $transaction->actionCreated > $yearEnd) {
				$this->consumeBuyLots($buys, $transaction, $splits, $yearEnd);
				continue;
			}

			$totalFees = $totalFees->add($transaction->feeDefaultCurrency);

			$this->processSellTransaction(
				$buys,
				$transaction,
				$ticker,
				$splits,
				$yearEnd,
				$transactions,
				$totalSalesProceeds,
				$totalCostBasis,
				$totalGains,
				$totalLosses,
			);
		}
	}

	/**
	 * @param array<int, TransactionBuyDto> $buys
	 * @param list<SplitDto> $splits
	 */
	private function addBuyLot(array &$buys, Transaction $transaction, array $splits, DateTimeImmutable $yearEnd): void
	{
		$splitFactor = $this->countSplitFactor($transaction->actionCreated, $yearEnd, $splits);

		$buys[] = new TransactionBuyDto(
			brokerId: $transaction->brokerId,
			actionCreated: $transaction->actionCreated,
			units: $transaction->units,
			priceTickerCurrency: $transaction->priceTickerCurrency,
			priceDefaultCurrency: $transaction->priceDefaultCurrency,
			priceWithSplitTickerCurrency: $transaction->priceTickerCurrency->div($splitFactor),
			priceWithSplitDefaultCurrency: $transaction->priceDefaultCurrency->div($splitFactor),
		);
	}

	/**
	 * @param array<int, TransactionBuyDto> $buys
	 * @param list<SplitDto> $splits
	 * @param list<TaxReportRealizedGainTransactionDto> $transactions
	 */
	private function processSellTransaction(
		array &$buys,
		Transaction $transaction,
		Ticker $ticker,
		array $splits,
		DateTimeImmutable $yearEnd,
		array &$transactions,
		Decimal &$totalSalesProceeds,
		Decimal &$totalCostBasis,
		Decimal &$totalGains,
		Decimal &$totalLosses,
	): void {
		$buysForBroker = array_filter($buys, fn(TransactionBuyDto $buy) => $buy->brokerId === $transaction->brokerId);

		$sellSplitFactor = $this->countSplitFactor($transaction->actionCreated, $yearEnd, $splits);
		$transactionUnitsAbs = $transaction->units->abs()->mul($sellSplitFactor);
		$sumBuyUnits = new Decimal(0, 18);
		$sellFee = $transaction->feeDefaultCurrency;

		foreach ($buysForBroker as $buyKey => $buy) {
			$buySplitFactor = $this->countSplitFactor($buy->actionCreated, $transaction->actionCreated, $splits);
			$buyUnitsWithSplits = $buy->units->mul($buySplitFactor);

			$sellProceeds = $buyUnitsWithSplits->mul($transaction->priceDefaultCurrency);
			$costBasis = $buy->units->mul($buy->priceDefaultCurrency);
			$gainLoss = $sellProceeds->sub($costBasis);

			$transactions[] = new TaxReportRealizedGainTransactionDto(
				tickerTicker: $ticker->ticker,
				tickerName: $ticker->name,
				buyDate: $buy->actionCreated->format('Y-m-d'),
				sellDate: $transaction->actionCreated->format('Y-m-d'),
				holdingPeriodDays: (int) $transaction->actionCreated->diff($buy->actionCreated)->days,
				units: $buyUnitsWithSplits,
				buyPrice: $buy->priceDefaultCurrency,
				sellPrice: $transaction->priceDefaultCurrency,
				costBasis: $costBasis,
				salesProceeds: $sellProceeds,
				fee: $sellFee,
				gainLoss: $gainLoss,
			);

			$totalSalesProceeds = $totalSalesProceeds->add($sellProceeds);
			$totalCostBasis = $totalCostBasis->add($costBasis);

			if ($gainLoss->isPositive()) {
				$totalGains = $totalGains->add($gainLoss);
			} else {
				$totalLosses = $totalLosses->add($gainLoss->abs());
			}

			$sellFee = new Decimal(0);
			$sumBuyUnits = $sumBuyUnits->add($buyUnitsWithSplits);

			if ($sumBuyUnits <= $transactionUnitsAbs) {
				unset($buys[$buyKey]);
			} else {
				$buys[$buyKey]->units = $sumBuyUnits->sub($transactionUnitsAbs)->div($buySplitFactor);
			}

			if ($sumBuyUnits >= $transactionUnitsAbs) {
				break;
			}
		}
	}

	/**
	 * @param array<int, TransactionBuyDto> $buys
	 * @param list<SplitDto> $splits
	 */
	private function consumeBuyLots(array &$buys, Transaction $transaction, array $splits, DateTimeImmutable $yearEnd): void
	{
		$buysForBroker = array_filter($buys, fn(TransactionBuyDto $buy) => $buy->brokerId === $transaction->brokerId);

		$sellSplitFactor = $this->countSplitFactor($transaction->actionCreated, $yearEnd, $splits);
		$transactionUnitsAbs = $transaction->units->abs()->mul($sellSplitFactor);
		$sumBuyUnits = new Decimal(0, 18);

		foreach ($buysForBroker as $buyKey => $buy) {
			$buySplitFactor = $this->countSplitFactor($buy->actionCreated, $transaction->actionCreated, $splits);
			$buyUnitsWithSplits = $buy->units->mul($buySplitFactor);

			$sumBuyUnits = $sumBuyUnits->add($buyUnitsWithSplits);

			if ($sumBuyUnits <= $transactionUnitsAbs) {
				unset($buys[$buyKey]);
			} else {
				$buys[$buyKey]->units = $sumBuyUnits->sub($transactionUnitsAbs)->div($buySplitFactor);
			}

			if ($sumBuyUnits >= $transactionUnitsAbs) {
				break;
			}
		}
	}

	/** @param list<SplitDto> $splits */
	private function countSplitFactor(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo, array $splits): Decimal
	{
		$splitFactor = new Decimal(1, 8);

		foreach ($splits as $split) {
			if ($split->date >= $dateFrom && $split->date <= $dateTo) {
				$splitFactor = $splitFactor->mul($split->factor);
			}
		}

		return $splitFactor;
	}
}
