<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
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
			orderDirection: OrderDirectionEnum::ASC,
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

		$defaultCurrency = $user->getDefaultCurrency();
		$tickerCurrency = $asset->getTicker()->getCurrency();

		$exchangeRate = $this->exchangeRateProvider->getExchangeRate(
			$dateTime,
			$tickerCurrency,
			$user->getDefaultCurrency(),
		);

		$firstTransaction = $transactions[array_key_first($transactions)];
		$fromFirstTransactionDays = (int) $dateTime->diff($firstTransaction->getActionCreated())->days;

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

			$splitFactor = new Decimal(1);

			foreach ($splits as $split) {
				if ($split->getDate() >= $transaction->getActionCreated() && $split->getDate() <= $dateTime) {
					$splitFactor = $splitFactor->mul($split->getFactor());
				}
			}

			$transactionUnits = $transaction->getUnits();

			$units = $units->add($transactionUnits->mul($splitFactor));

			$transactionSum = $transactionUnits->mul($transaction->getPriceTickerCurrency());
			$transactionSumDefaultCurrency = $transactionUnits->mul($transaction->getPriceDefaultCurrency());

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
}
