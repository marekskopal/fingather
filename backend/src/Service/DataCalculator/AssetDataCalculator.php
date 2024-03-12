<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Provider\Dto\AssetDataDto;
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
		);
		if (count($transactions) === 0) {
			return null;
		}

		$splits = $this->splitProvider->getSplits($asset->getTicker());

		$transactionValue = new Decimal(0);
		$transactionValueDefaultCurrency = new Decimal(0);
		$units = new Decimal(0);
		$dividendTotal = new Decimal(0);

		$tickerCurrency = $asset->getTicker()->getCurrency();

		$firstTransaction = $transactions[array_key_last($transactions)];
		$fromFirstTransactionDays = (int) $dateTime->diff($firstTransaction->getActionCreated())->days;

		foreach ($transactions as $transaction) {
			if ($transaction->getActionType() === TransactionActionTypeEnum::Dividend) {
				$dividendExchangeRate = $this->exchangeRateProvider->getExchangeRate(
					$transaction->getActionCreated(),
					$transaction->getCurrency(),
					$tickerCurrency,
				);

				$dividendTotal = $dividendTotal->add($transaction->getPrice()->mul($dividendExchangeRate));

				continue;
			}

			$splitFactor = new Decimal(1);

			foreach ($splits as $split) {
				if ($split->getDate() >= $transaction->getActionCreated() && $split->getDate() <= $dateTime) {
					$splitFactor = $splitFactor->mul($split->getFactor());
				}
			}

			$transactionUnits = $transaction->getUnits();
			$transactionPriceUnit = $transaction->getPrice();

			$units = $units->add($transactionUnits->mul($splitFactor));

			$transactionSum = $transactionUnits->mul($transactionPriceUnit);

			if ($tickerCurrency->getId() !== $transaction->getCurrency()->getId()) {
				$transactionExchangeRate = $this->exchangeRateProvider->getExchangeRate(
					$transaction->getActionCreated(),
					$transaction->getCurrency(),
					$tickerCurrency,
				);

				$transactionSum = $transactionSum->mul($transactionExchangeRate);
			}

			$transactionValue = $transactionValue->add($transactionSum);

			$transactionExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
				$transaction->getActionCreated(),
				$tickerCurrency,
				$user->getDefaultCurrency(),
			);

			$transactionValueDefaultCurrency = $transactionValueDefaultCurrency->add(
				$transactionSum->mul($transactionExchangeRateDefaultCurrency),
			);
		}

		$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
		$price = $lastTickerData?->getClose() ?? new Decimal(0);

		$exchangeRate = $this->exchangeRateProvider->getExchangeRate(
			$dateTime,
			$tickerCurrency,
			$user->getDefaultCurrency(),
		);

		$value = $units->mul($price);
		$gain = $value->sub($transactionValue);
		$gainDefaultCurrency = $gain->mul($exchangeRate);
		$dividendGainDefaultCurrency = $dividendTotal->mul($exchangeRate);
		$fxImpact = $transactionValue->mul($exchangeRate)->sub($transactionValueDefaultCurrency);

		$gainPercentage = CalculatorUtils::toPercentage($gain, $transactionValue);
		$gainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($gainPercentage, $fromFirstTransactionDays);
		$dividendGainPercentage = CalculatorUtils::toPercentage($dividendTotal, $transactionValue);
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
			dividendGain: $dividendTotal,
			dividendGainDefaultCurrency: $dividendGainDefaultCurrency,
			dividendGainPercentage: $dividendGainPercentage,
			dividendGainPercentagePerAnnum: $dividendGainPercentagePerAnnum,
			fxImpact: $fxImpact,
			fxImpactPercentage: $fxImpactPercentage,
			fxImpactPercentagePerAnnum: $fxImpactPercentagePerAnnum,
			return: $gainDefaultCurrency->add($dividendGainDefaultCurrency)->add($fxImpact),
			returnPercentage: round($gainPercentage + $dividendGainPercentage + $fxImpactPercentage, 2),
			returnPercentagePerAnnum: round($gainPercentagePerAnnum + $dividendGainPercentagePerAnnum + $fxImpactPercentagePerAnnum, 2),
			firstTransactionActionCreated: $firstTransaction->getActionCreated(),
		);
	}
}
