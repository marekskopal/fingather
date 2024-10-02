<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Model\Repository\Enum\TransactionOrderByEnum;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\DataCalculator\Dto\TransactionValueDto;
use FinGather\Service\DataCalculator\Dto\ValueDto;
use FinGather\Service\Provider\ExchangeRateProvider;
use FinGather\Service\Provider\SplitProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Utils\CalculatorUtils;

final class AssetDataCalculator
{
	public function __construct(
		private readonly TransactionProvider $transactionProvider,
		private readonly SplitProvider $splitProvider,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly ExchangeRateProvider $exchangeRateProvider,
	) {
	}

	public function calculate(User $user, Portfolio $portfolio, Asset $asset, DateTimeImmutable $dateTime): ?AssetDataDto
	{
		$transactions = $this->transactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			asset: $asset,
			actionCreatedBefore: $dateTime,
			actionTypes: [
				TransactionActionTypeEnum::Buy,
				TransactionActionTypeEnum::Sell,
				TransactionActionTypeEnum::Dividend,
				TransactionActionTypeEnum::Tax,
				TransactionActionTypeEnum::Fee,
				TransactionActionTypeEnum::DividendTax,
			],
			orderBy: [
				TransactionOrderByEnum::BrokerId->value => OrderDirectionEnum::ASC,
				TransactionOrderByEnum::ActionCreated->value => OrderDirectionEnum::ASC,
			],
		);
		if (count($transactions) === 0) {
			return null;
		}

		$splits = $this->splitProvider->getSplits($asset->getTicker());

		$units = new Decimal(0);
		$dividendYield = new Decimal(0);
		$dividendYieldDefaultCurrency = new Decimal(0);
		$dividendYieldTickerCurrency = new Decimal(0);
		$tax = new Decimal(0);
		$taxDefaultCurrency = new Decimal(0);
		$fee = new Decimal(0);
		$feeDefaultCurrency = new Decimal(0);
		$realizedGain = new Decimal(0);
		$realizedGainDefaultCurrency = new Decimal(0);

		$defaultCurrency = $portfolio->getCurrency();
		$tickerCurrency = $asset->getTicker()->getCurrency();

		$exchangeRate = $this->exchangeRateProvider->getExchangeRate(
			$dateTime,
			$tickerCurrency,
			$portfolio->getCurrency(),
		);

		$firstTransaction = $transactions[array_key_first($transactions)];
		$fromFirstTransactionDays = (int) $dateTime->diff($firstTransaction->getActionCreated())->days;

		$buys = [];

		foreach ($transactions as $transaction) {
			$this->processTransaction(
				$transaction,
				$dateTime,
				$defaultCurrency,
				$splits,
				$buys,
				$units,
				$realizedGain,
				$realizedGainDefaultCurrency,
				$dividendYield,
				$dividendYieldDefaultCurrency,
				$dividendYieldTickerCurrency,
				$tax,
				$taxDefaultCurrency,
				$fee,
				$feeDefaultCurrency,
			);
		}

		if ($units->isNegative()) {
			$units = new Decimal(0);
		}

		$transactionValue = $this->countTransactionValue($buys);

		$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
		$price = $lastTickerData?->getClose() ?? new Decimal(0);

		$value = $units->mul($price);
		$gain = $value->sub($transactionValue->value);
		$gainDefaultCurrency = $gain->mul($exchangeRate);
		$dividendYieldDefaultCurrency = $dividendYieldDefaultCurrency->add($dividendYieldTickerCurrency->mul($exchangeRate));
		$fxImpact = $transactionValue->value->mul($exchangeRate)->sub($transactionValue->valueDefaultCurrency);

		$gainPercentage = CalculatorUtils::toPercentage($gain, $transactionValue->value);
		$gainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($gainPercentage, $fromFirstTransactionDays);
		$dividendYieldPercentage = CalculatorUtils::toPercentage($dividendYield, $transactionValue->value);
		$dividendYieldPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($dividendYieldPercentage, $fromFirstTransactionDays);
		$fxImpactPercentage = CalculatorUtils::toPercentage($fxImpact, $transactionValue->valueDefaultCurrency);
		$fxImpactPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($fxImpactPercentage, $fromFirstTransactionDays);

		return new AssetDataDto(
			price: $price,
			units: $units,
			value: $value->mul($exchangeRate),
			transactionValue: $transactionValue->value,
			transactionValueDefaultCurrency: $transactionValue->valueDefaultCurrency,
			averagePrice: $transactionValue->averagePrice,
			averagePriceDefaultCurrency: $transactionValue->averagePriceDefaultCurrency,
			gain: $gain,
			gainDefaultCurrency: $gainDefaultCurrency,
			gainPercentage: $gainPercentage,
			gainPercentagePerAnnum: $gainPercentagePerAnnum,
			realizedGain: $realizedGain,
			realizedGainDefaultCurrency: $realizedGainDefaultCurrency,
			dividendYield: $dividendYield,
			dividendYieldDefaultCurrency: $dividendYieldDefaultCurrency,
			dividendYieldPercentage: $dividendYieldPercentage,
			dividendYieldPercentagePerAnnum: $dividendYieldPercentagePerAnnum,
			fxImpact: $fxImpact,
			fxImpactPercentage: $fxImpactPercentage,
			fxImpactPercentagePerAnnum: $fxImpactPercentagePerAnnum,
			return: $gainDefaultCurrency->add($dividendYieldDefaultCurrency)->add($fxImpact),
			returnPercentage: round($gainPercentage + $dividendYieldPercentage + $fxImpactPercentage, 2),
			returnPercentagePerAnnum: round($gainPercentagePerAnnum + $dividendYieldPercentagePerAnnum + $fxImpactPercentagePerAnnum, 2),
			tax: $tax,
			taxDefaultCurrency: $taxDefaultCurrency,
			fee: $fee,
			feeDefaultCurrency: $feeDefaultCurrency,
			firstTransactionActionCreated: $firstTransaction->getActionCreated(),
		);
	}

	/**
	 * @param list<Split> $splits
	 * @param array<int, TransactionBuyDto> $buys
	 */
	private function processTransaction(
		Transaction $transaction,
		DateTimeImmutable $dateTime,
		Currency $defaultCurrency,
		array $splits,
		array &$buys,
		Decimal &$units,
		Decimal &$realizedGain,
		Decimal &$realizedGainDefaultCurrency,
		Decimal &$dividendYield,
		Decimal &$dividendYieldDefaultCurrency,
		Decimal &$dividendYieldTickerCurrency,
		Decimal &$tax,
		Decimal &$taxDefaultCurrency,
		Decimal &$fee,
		Decimal &$feeDefaultCurrency,
	): void {
		$tax = $tax->add($transaction->getTaxTickerCurrency());
		$taxDefaultCurrency = $taxDefaultCurrency->add($transaction->getTaxDefaultCurrency());
		$fee = $fee->add($transaction->getFeeTickerCurrency());
		$feeDefaultCurrency = $feeDefaultCurrency->add($transaction->getFeeDefaultCurrency());

		if (
			$transaction->getActionType() === TransactionActionTypeEnum::Tax
			|| $transaction->getActionType() === TransactionActionTypeEnum::Fee
			|| $transaction->getActionType() === TransactionActionTypeEnum::DividendTax
		) {
			return;
		}

		if ($transaction->getActionType() === TransactionActionTypeEnum::Dividend) {
			$dividendTransactionValue = $transaction->getPriceTickerCurrency();

			$dividendYield = $dividendYield->add($dividendTransactionValue);

			if ($transaction->getCurrency()->getId() === $defaultCurrency->getId()) {
				$dividendYieldDefaultCurrency = $dividendYieldDefaultCurrency->add($transaction->getPrice());
			} else {
				$dividendYieldTickerCurrency = $dividendYieldTickerCurrency->add($dividendTransactionValue);
			}

			return;
		}

		$splitFactor = $this->countSplitFactor($transaction->getActionCreated(), $dateTime, $splits);

		$transactionUnits = $transaction->getUnits();
		$transactionUnitsWithSplit = $transactionUnits->mul($splitFactor);

		$units = $units->add($transactionUnitsWithSplit);

		if ($transaction->getActionType() === TransactionActionTypeEnum::Buy) {
			$buys[] = new TransactionBuyDto(
				brokerId: $transaction->getBrokerId(),
				actionCreated: $transaction->getActionCreated(),
				units: $transactionUnits,
				priceTickerCurrency: $transaction->getPriceTickerCurrency(),
				priceDefaultCurrency: $transaction->getPriceDefaultCurrency(),
				priceWithSplitTickerCurrency: $transaction->getPriceTickerCurrency()->div($splitFactor),
				priceWithSplitDefaultCurrency: $transaction->getPriceDefaultCurrency()->div($splitFactor),
			);
		}

		if ($transaction->getActionType() !== TransactionActionTypeEnum::Sell) {
			return;
		}

		$transactionRealizedGain = $this->countTransactionRealizedGain($buys, $transaction, $transactionUnitsWithSplit, $splits);

		$realizedGain = $realizedGain->add($transactionRealizedGain->value);
		$realizedGainDefaultCurrency = $realizedGainDefaultCurrency->add($transactionRealizedGain->valueDefaultCurrency);
	}

	/** @param list<Split> $splits */
	private function countSplitFactor(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo, array $splits): Decimal
	{
		$splitFactor = new Decimal(1, 8);

		foreach ($splits as $split) {
			if ($split->getDate() >= $dateFrom && $split->getDate() <= $dateTo) {
				$splitFactor = $splitFactor->mul($split->getFactor());
			}
		}

		return $splitFactor;
	}

	/** @param array<int, TransactionBuyDto> $buys */
	private function countTransactionValue(array $buys): TransactionValueDto
	{
		$transactionValue = new Decimal(0);
		$transactionValueDefaultCurrency = new Decimal(0);
		$averagePrice = new Decimal(0);
		$averagePriceDefaultCurrency = new Decimal(0);
		$priceSum = new Decimal(0);
		$priceSumDefaultCurrency = new Decimal(0);

		foreach ($buys as $buy) {
			$transactionSum = $buy->units->mul($buy->priceTickerCurrency);
			$transactionSumDefaultCurrency = $buy->units->mul($buy->priceDefaultCurrency);

			$priceSum = $priceSum->add($buy->priceWithSplitTickerCurrency);
			$priceSumDefaultCurrency = $priceSumDefaultCurrency->add($buy->priceWithSplitDefaultCurrency);

			$transactionValue = $transactionValue->add($transactionSum);
			$transactionValueDefaultCurrency = $transactionValueDefaultCurrency->add($transactionSumDefaultCurrency);
		}

		$buysCount = count($buys);
		if ($buysCount > 0) {
			$averagePrice = $priceSum->div($buysCount);
			$averagePriceDefaultCurrency = $priceSumDefaultCurrency->div($buysCount);
		}

		return new TransactionValueDto(
			value: $transactionValue,
			valueDefaultCurrency: $transactionValueDefaultCurrency,
			averagePrice: $averagePrice,
			averagePriceDefaultCurrency: $averagePriceDefaultCurrency,
		);
	}

	/**
	 * @param array<int, TransactionBuyDto> $buys
	 * @param list<Split> $splits
	 */
	private function countTransactionRealizedGain(
		array &$buys,
		Transaction $transaction,
		Decimal $transactionUnitsWithSplit,
		array $splits,
	): ValueDto
	{
		$transactionRealizedGain = new Decimal(0);
		$transactionRealizedGainDefaultCurrency = new Decimal(0);

		$sumBuyUnits = new Decimal(0, 18);

		$buysForBroker = array_filter($buys, fn(TransactionBuyDto $buy) => $buy->brokerId === $transaction->getBrokerId());

		foreach ($buysForBroker as $buyKey => $buy) {
			$buySplitFactor = $this->countSplitFactor($buy->actionCreated, $transaction->getActionCreated(), $splits);

			$buyUnitsWithSplits = $buy->units->mul($buySplitFactor);

			$sellValue = $buyUnitsWithSplits->mul($transaction->getPriceTickerCurrency());
			$sellValueDefaultCurrency = $buyUnitsWithSplits->mul($transaction->getPriceDefaultCurrency());

			$buyValue = $buy->units->mul($buy->priceTickerCurrency);
			$buyValueDefaultCurrency = $buy->units->mul($buy->priceDefaultCurrency);

			$transactionRealizedGain = $transactionRealizedGain->add($sellValue->sub($buyValue));
			$transactionRealizedGainDefaultCurrency = $transactionRealizedGainDefaultCurrency->add(
				$sellValueDefaultCurrency->sub($buyValueDefaultCurrency),
			);

			$sumBuyUnits = $sumBuyUnits->add($buyUnitsWithSplits);
			$transactionUnitsAbs = $transactionUnitsWithSplit->abs();

			if ($sumBuyUnits <= $transactionUnitsAbs) {
				unset($buys[$buyKey]);
			} else {
				$unitsDiffWithSplit = $sumBuyUnits->sub($transactionUnitsAbs);

				$buys[$buyKey]->units = $unitsDiffWithSplit->div($buySplitFactor);
			}
			if ($sumBuyUnits >= $transactionUnitsAbs) {
				break;
			}
		}

		return new ValueDto(value: $transactionRealizedGain, valueDefaultCurrency: $transactionRealizedGainDefaultCurrency);
	}
}
