<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Model\Repository\Enum\TransactionOrderByEnum;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\Provider\ExchangeRateProvider;
use FinGather\Service\Provider\SplitProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Utils\CalculatorUtils;
use Safe\DateTimeImmutable;

class AssetDataCalculator
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
			actionTypes: [TransactionActionTypeEnum::Buy, TransactionActionTypeEnum::Sell, TransactionActionTypeEnum::Dividend],
			orderBy: [
				TransactionOrderByEnum::BrokerId->value => OrderDirectionEnum::ASC,
				TransactionOrderByEnum::ActionCreated->value => OrderDirectionEnum::ASC,
			],
		);
		if (count($transactions) === 0) {
			return null;
		}

		$splits = $this->splitProvider->getSplits($asset->getTicker());

		$transactionValue = new Decimal(0);
		$transactionValueDefaultCurrency = new Decimal(0);
		$units = new Decimal(0);
		$dividendGain = new Decimal(0);
		$dividendGainDefaultCurrency = new Decimal(0);
		$dividendGainTickerCurrency = new Decimal(0);
		$tax = new Decimal(0);
		$taxDefaultCurrency = new Decimal(0);
		$fee = new Decimal(0);
		$feeDefaultCurrency = new Decimal(0);
		$realizedGain = new Decimal(0);
		$realizedGainDefaultCurrency = new Decimal(0);

		$defaultCurrency = $user->getDefaultCurrency();
		$tickerCurrency = $asset->getTicker()->getCurrency();

		$exchangeRate = $this->exchangeRateProvider->getExchangeRate(
			$dateTime,
			$tickerCurrency,
			$user->getDefaultCurrency(),
		);

		$firstTransaction = $transactions[array_key_first($transactions)];
		$fromFirstTransactionDays = (int) $dateTime->diff($firstTransaction->getActionCreated())->days;

		$buys = [];

		foreach ($transactions as $transaction) {
			$tax = $tax->add($transaction->getTaxTickerCurrency());
			$taxDefaultCurrency = $taxDefaultCurrency->add($transaction->getTaxDefaultCurrency());
			$fee = $fee->add($transaction->getFeeTickerCurrency());
			$feeDefaultCurrency = $feeDefaultCurrency->add($transaction->getFeeDefaultCurrency());

			if ($transaction->getActionType() === TransactionActionTypeEnum::Dividend) {
				$dividendTransactionValue = $transaction->getPriceTickerCurrency();

				$dividendGain = $dividendGain->add($dividendTransactionValue);

				if ($transaction->getCurrency()->getId() === $defaultCurrency->getId()) {
					$dividendGainDefaultCurrency = $dividendGainDefaultCurrency->add($transaction->getPrice());
				} else {
					$dividendGainTickerCurrency = $dividendGainTickerCurrency->add($dividendTransactionValue);
				}

				continue;
			}

			$splitFactor = $this->countSplitFactor($transaction->getActionCreated(), $dateTime, $splits);

			$transactionUnits = $transaction->getUnits();
			$transactionUnitsWithSplit = $transactionUnits->mul($splitFactor);

			$units = $units->add($transactionUnitsWithSplit);

			if ($transaction->getActionType() === TransactionActionTypeEnum::Buy) {
				$buys[] = new TransactionBuyDto(
					actionCreated: $transaction->getActionCreated(),
					units: $transactionUnits,
					priceTickerCurrency: $transaction->getPriceTickerCurrency(),
					priceDefaultCurrency: $transaction->getPriceDefaultCurrency(),
				);
			}

			if ($transaction->getActionType() !== TransactionActionTypeEnum::Sell) {
				continue;
			}

			$transactionRealizedGain = new Decimal(0);
			$transactionRealizedGainDefaultCurrency = new Decimal(0);

			$sumBuyUnits = new Decimal(0, 18);

			foreach ($buys as $buyKey => $buy) {
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

			$realizedGain = $realizedGain->add($transactionRealizedGain);
			$realizedGainDefaultCurrency = $realizedGainDefaultCurrency->add($transactionRealizedGainDefaultCurrency);
		}

		foreach ($buys as $buy) {
			$transactionSum = $buy->units->mul($buy->priceTickerCurrency);
			$transactionSumDefaultCurrency = $buy->units->mul($buy->priceDefaultCurrency);

			$transactionValue = $transactionValue->add($transactionSum);
			$transactionValueDefaultCurrency = $transactionValueDefaultCurrency->add($transactionSumDefaultCurrency);
		}

		$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
		$price = $lastTickerData?->getClose() ?? new Decimal(0);

		$value = $units->mul($price);
		$gain = $value->sub($transactionValue);
		$gainDefaultCurrency = $gain->mul($exchangeRate);
		$dividendGainDefaultCurrency = $dividendGainDefaultCurrency->add($dividendGainTickerCurrency->mul($exchangeRate));
		$fxImpact = $transactionValue->mul($exchangeRate)->sub($transactionValueDefaultCurrency);

		$gainPercentage = CalculatorUtils::toPercentage($gain, $transactionValue);
		$gainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($gainPercentage, $fromFirstTransactionDays);
		$dividendGainPercentage = CalculatorUtils::toPercentage($dividendGain, $transactionValue);
		$dividendGainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($dividendGainPercentage, $fromFirstTransactionDays);
		$fxImpactPercentage = CalculatorUtils::toPercentage($fxImpact, $transactionValueDefaultCurrency);
		$fxImpactPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($fxImpactPercentage, $fromFirstTransactionDays);

		return new AssetDataDto(
			price: $price,
			units: $units,
			value: $value->mul($exchangeRate),
			transactionValue: $transactionValue,
			transactionValueDefaultCurrency: $transactionValueDefaultCurrency,
			gain: $gain,
			gainDefaultCurrency: $gainDefaultCurrency,
			gainPercentage: $gainPercentage,
			gainPercentagePerAnnum: $gainPercentagePerAnnum,
			realizedGain: $realizedGain,
			realizedGainDefaultCurrency: $realizedGainDefaultCurrency,
			dividendGain: $dividendGain,
			dividendGainDefaultCurrency: $dividendGainDefaultCurrency,
			dividendGainPercentage: $dividendGainPercentage,
			dividendGainPercentagePerAnnum: $dividendGainPercentagePerAnnum,
			fxImpact: $fxImpact,
			fxImpactPercentage: $fxImpactPercentage,
			fxImpactPercentagePerAnnum: $fxImpactPercentagePerAnnum,
			return: $gainDefaultCurrency->add($dividendGainDefaultCurrency)->add($fxImpact),
			returnPercentage: round($gainPercentage + $dividendGainPercentage + $fxImpactPercentage, 2),
			returnPercentagePerAnnum: round($gainPercentagePerAnnum + $dividendGainPercentagePerAnnum + $fxImpactPercentagePerAnnum, 2),
			tax: $tax,
			taxDefaultCurrency: $taxDefaultCurrency,
			fee: $fee,
			feeDefaultCurrency: $feeDefaultCurrency,
			firstTransactionActionCreated: $firstTransaction->getActionCreated(),
		);
	}

	/** @param list<Split> $splits */
	private function countSplitFactor(\DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo, array $splits): Decimal
	{
		$splitFactor = new Decimal(1, 8);

		foreach ($splits as $split) {
			if ($split->getDate() >= $dateFrom && $split->getDate() <= $dateTo) {
				$splitFactor = $splitFactor->mul($split->getFactor());
			}
		}

		return $splitFactor;
	}
}
