<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainsDto;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainTransactionDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\DataCalculator\LotMatcher\LotMatcherFactory;
use FinGather\Service\DataCalculator\LotMatcher\LotMatcherInterface;
use FinGather\Service\Provider\CurrentTransactionProviderInterface;
use FinGather\Service\Provider\Dto\SplitDto;
use FinGather\Service\Provider\SplitProviderInterface;
use FinGather\Utils\CalculatorUtils;

final readonly class TaxReportRealizedGainsCalculator implements TaxReportRealizedGainsCalculatorInterface
{
	public function __construct(
		private CurrentTransactionProviderInterface $currentTransactionProvider,
		private SplitProviderInterface $splitProvider,
		private LotMatcherFactory $lotMatcherFactory,
	) {
	}

	public function calculate(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $yearStart,
		DateTimeImmutable $yearEnd,
		CostBasisMethodEnum $method,
	): TaxReportRealizedGainsDto {
		$allTransactionsByAsset = $this->currentTransactionProvider->loadTransactions(user: $user, portfolio: $portfolio);
		$lotMatcher = $this->lotMatcherFactory->forMethod($method);

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
				$lotMatcher,
				$transactions,
				$totalSalesProceeds,
				$totalCostBasis,
				$totalGains,
				$totalLosses,
				$totalFees,
			);
		}

		return new TaxReportRealizedGainsDto(
			method: $method,
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
		LotMatcherInterface $lotMatcher,
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
				$this->consumeBuyLots($buys, $transaction, $splits, $yearEnd, $lotMatcher);
				continue;
			}

			$totalFees = $totalFees->add($transaction->feeDefaultCurrency);

			$this->processSellTransaction(
				$buys,
				$transaction,
				$ticker,
				$splits,
				$yearEnd,
				$lotMatcher,
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
		$splitFactor = CalculatorUtils::countSplitFactor($transaction->actionCreated, $yearEnd, $splits);

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
		LotMatcherInterface $lotMatcher,
		array &$transactions,
		Decimal &$totalSalesProceeds,
		Decimal &$totalCostBasis,
		Decimal &$totalGains,
		Decimal &$totalLosses,
	): void {
		$sellSplitFactor = CalculatorUtils::countSplitFactor($transaction->actionCreated, $yearEnd, $splits);
		$sellFee = $transaction->feeDefaultCurrency;

		$matches = $lotMatcher->consumeLots(
			$buys,
			$transaction->brokerId,
			$transaction->actionCreated,
			$transaction->units->abs()->mul($sellSplitFactor),
			$splits,
		);

		foreach ($matches as $match) {
			$sellProceeds = $match->usedUnitsWithSplits->mul($transaction->priceDefaultCurrency);
			$costBasis = $match->usedOriginalUnits->mul($match->buy->priceDefaultCurrency);
			$gainLoss = $sellProceeds->sub($costBasis);

			$transactions[] = new TaxReportRealizedGainTransactionDto(
				tickerTicker: $ticker->ticker,
				tickerName: $ticker->name,
				tickerLogo: $ticker->logo,
				buyDate: $match->buy->actionCreated->format('Y-m-d'),
				sellDate: $transaction->actionCreated->format('Y-m-d'),
				holdingPeriodDays: $transaction->actionCreated->diff($match->buy->actionCreated)->days,
				units: $match->usedUnitsWithSplits,
				buyPrice: $match->buy->priceDefaultCurrency,
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
		}
	}

	/**
	 * @param array<int, TransactionBuyDto> $buys
	 * @param list<SplitDto> $splits
	 */
	private function consumeBuyLots(
		array &$buys,
		Transaction $transaction,
		array $splits,
		DateTimeImmutable $yearEnd,
		LotMatcherInterface $lotMatcher,
	): void {
		$sellSplitFactor = CalculatorUtils::countSplitFactor($transaction->actionCreated, $yearEnd, $splits);

		$lotMatcher->consumeLots(
			$buys,
			$transaction->brokerId,
			$transaction->actionCreated,
			$transaction->units->abs()->mul($sellSplitFactor),
			$splits,
		);
	}
}
