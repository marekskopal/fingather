<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\TransactionAccumulatorDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\DataCalculator\Dto\TransactionValueDto;
use FinGather\Service\DataCalculator\Dto\ValueDto;
use FinGather\Service\Provider\CurrentTransactionProviderInterface;
use FinGather\Service\Provider\Dto\SplitDto;
use FinGather\Service\Provider\ExchangeRateProviderInterface;
use FinGather\Service\Provider\SplitProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Utils\CalculatorUtils;

final readonly class AssetDataCalculator implements AssetDataCalculatorInterface
{
	public function __construct(
		private CurrentTransactionProviderInterface $currentTransactionProvider,
		private SplitProviderInterface $splitProvider,
		private TickerDataProviderInterface $tickerDataProvider,
		private ExchangeRateProviderInterface $exchangeRateProvider,
	) {
	}

	public function calculate(User $user, Portfolio $portfolio, Asset $asset, DateTimeImmutable $dateTime): ?AssetDataDto
	{
		$transactions = $this->currentTransactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			asset: $asset,
			actionCreatedBefore: $dateTime,
		);
		if (count($transactions) === 0) {
			return null;
		}

		$splits = $this->splitProvider->getSplits($asset->ticker);

		$defaultCurrency = $portfolio->currency;
		$tickerCurrency = $asset->ticker->currency;

		$exchangeRate = $this->exchangeRateProvider->getExchangeRate($dateTime, $tickerCurrency, $portfolio->currency);

		$firstTransaction = array_first($transactions);
		$fromFirstTransactionDays = (int) $dateTime->diff($firstTransaction->actionCreated)->days;

		$accumulator = new TransactionAccumulatorDto();

		foreach ($transactions as $transaction) {
			$this->processTransaction($transaction, $dateTime, $defaultCurrency, $splits, $accumulator);
		}

		if ($accumulator->units->isNegative()) {
			$accumulator->units = new Decimal(0);
		}

		$transactionValue = $this->countTransactionValue($accumulator->buys, $accumulator->units);

		$lastTickerDataClose = $this->tickerDataProvider->getLastTickerDataClose($asset->ticker, $dateTime);
		$price = $lastTickerDataClose ?? new Decimal(0);

		$value = $accumulator->units->mul($price);
		$gain = $value->sub($transactionValue->value);
		$gainDefaultCurrency = $gain->mul($exchangeRate);
		$dividendYieldDefaultCurrency = $accumulator->dividendYieldDefaultCurrency->add(
			$accumulator->dividendYieldTickerCurrency->mul($exchangeRate),
		);
		$fxImpact = $transactionValue->value->mul($exchangeRate)->sub($transactionValue->valueDefaultCurrency);

		$gainPercentage = CalculatorUtils::toPercentage($gain, $transactionValue->value);
		$gainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($gainPercentage, $fromFirstTransactionDays);
		$dividendYieldPercentage = CalculatorUtils::toPercentage($accumulator->dividendYield, $transactionValue->value);
		$dividendYieldPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($dividendYieldPercentage, $fromFirstTransactionDays);
		$fxImpactPercentage = CalculatorUtils::toPercentage($fxImpact, $transactionValue->valueDefaultCurrency);
		$fxImpactPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($fxImpactPercentage, $fromFirstTransactionDays);

		return new AssetDataDto(
			date: $dateTime,
			price: $price,
			units: $accumulator->units,
			value: $value->mul($exchangeRate),
			transactionValue: $transactionValue->value,
			transactionValueDefaultCurrency: $transactionValue->valueDefaultCurrency,
			averagePrice: $transactionValue->averagePrice,
			averagePriceDefaultCurrency: $transactionValue->averagePriceDefaultCurrency,
			gain: $gain,
			gainDefaultCurrency: $gainDefaultCurrency,
			gainPercentage: $gainPercentage,
			gainPercentagePerAnnum: $gainPercentagePerAnnum,
			realizedGain: $accumulator->realizedGain,
			realizedGainDefaultCurrency: $accumulator->realizedGainDefaultCurrency,
			dividendYield: $accumulator->dividendYield,
			dividendYieldDefaultCurrency: $dividendYieldDefaultCurrency,
			dividendYieldPercentage: $dividendYieldPercentage,
			dividendYieldPercentagePerAnnum: $dividendYieldPercentagePerAnnum,
			fxImpact: $fxImpact,
			fxImpactPercentage: $fxImpactPercentage,
			fxImpactPercentagePerAnnum: $fxImpactPercentagePerAnnum,
			return: $gainDefaultCurrency->add($dividendYieldDefaultCurrency)->add($fxImpact),
			returnPercentage: CalculatorUtils::sumPercentages($gainPercentage, $dividendYieldPercentage, $fxImpactPercentage),
			returnPercentagePerAnnum: CalculatorUtils::sumPercentages(
				$gainPercentagePerAnnum,
				$dividendYieldPercentagePerAnnum,
				$fxImpactPercentagePerAnnum,
			),
			tax: $accumulator->tax,
			taxDefaultCurrency: $accumulator->taxDefaultCurrency,
			fee: $accumulator->fee,
			feeDefaultCurrency: $accumulator->feeDefaultCurrency,
			firstTransactionActionCreated: $firstTransaction->actionCreated,
		);
	}

	/** @param list<SplitDto> $splits */
	private function processTransaction(
		Transaction $transaction,
		DateTimeImmutable $dateTime,
		Currency $defaultCurrency,
		array $splits,
		TransactionAccumulatorDto $accumulator,
	): void {
		$accumulator->tax = $accumulator->tax->add($transaction->taxTickerCurrency);
		$accumulator->taxDefaultCurrency = $accumulator->taxDefaultCurrency->add($transaction->taxDefaultCurrency);
		$accumulator->fee = $accumulator->fee->add($transaction->feeTickerCurrency);
		$accumulator->feeDefaultCurrency = $accumulator->feeDefaultCurrency->add($transaction->feeDefaultCurrency);

		if (
			$transaction->actionType === TransactionActionTypeEnum::Tax
			|| $transaction->actionType === TransactionActionTypeEnum::Fee
			|| $transaction->actionType === TransactionActionTypeEnum::DividendTax
		) {
			return;
		}

		if ($transaction->actionType === TransactionActionTypeEnum::Dividend) {
			$dividendTransactionValue = $transaction->priceTickerCurrency;

			$accumulator->dividendYield = $accumulator->dividendYield->add($dividendTransactionValue);

			if ($transaction->currency->id === $defaultCurrency->id) {
				$accumulator->dividendYieldDefaultCurrency = $accumulator->dividendYieldDefaultCurrency->add($transaction->price);
			} else {
				$accumulator->dividendYieldTickerCurrency = $accumulator->dividendYieldTickerCurrency->add($dividendTransactionValue);
			}

			return;
		}

		$splitFactor = CalculatorUtils::countSplitFactor($transaction->actionCreated, $dateTime, $splits);

		$transactionUnits = $transaction->units;
		$transactionUnitsWithSplit = $transactionUnits->mul($splitFactor);

		$accumulator->units = $accumulator->units->add($transactionUnitsWithSplit);

		if ($transaction->actionType === TransactionActionTypeEnum::Buy) {
			$accumulator->buys[] = new TransactionBuyDto(
				brokerId: $transaction->brokerId,
				actionCreated: $transaction->actionCreated,
				units: $transactionUnits,
				priceTickerCurrency: $transaction->priceTickerCurrency,
				priceDefaultCurrency: $transaction->priceDefaultCurrency,
				priceWithSplitTickerCurrency: $transaction->priceTickerCurrency->div($splitFactor),
				priceWithSplitDefaultCurrency: $transaction->priceDefaultCurrency->div($splitFactor),
			);
		}

		if ($transaction->actionType !== TransactionActionTypeEnum::Sell) {
			return;
		}

		$transactionRealizedGain = $this->countTransactionRealizedGain(
			$accumulator->buys,
			$transaction,
			$transactionUnitsWithSplit,
			$splits,
		);

		$accumulator->realizedGain = $accumulator->realizedGain->add($transactionRealizedGain->value);
		$accumulator->realizedGainDefaultCurrency = $accumulator->realizedGainDefaultCurrency->add(
			$transactionRealizedGain->valueDefaultCurrency,
		);
	}

	/** @param array<int, TransactionBuyDto> $buys */
	private function countTransactionValue(array $buys, Decimal $totalUnits): TransactionValueDto
	{
		$transactionValue = new Decimal(0);
		$transactionValueDefaultCurrency = new Decimal(0);
		$averagePrice = new Decimal(0);
		$averagePriceDefaultCurrency = new Decimal(0);

		foreach ($buys as $buy) {
			$transactionSum = $buy->units->mul($buy->priceTickerCurrency);
			$transactionSumDefaultCurrency = $buy->units->mul($buy->priceDefaultCurrency);

			$transactionValue = $transactionValue->add($transactionSum);
			$transactionValueDefaultCurrency = $transactionValueDefaultCurrency->add($transactionSumDefaultCurrency);
		}

		if (!$totalUnits->isZero()) {
			$averagePrice = $transactionValue->div($totalUnits);
			$averagePriceDefaultCurrency = $transactionValueDefaultCurrency->div($totalUnits);
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
	 * @param list<SplitDto> $splits
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

		$matches = FifoLotMatcher::consumeLots(
			$buys,
			$transaction->brokerId,
			$transaction->actionCreated,
			$transactionUnitsWithSplit->abs(),
			$splits,
		);

		foreach ($matches as $match) {
			$sellValue = $match->usedUnitsWithSplits->mul($transaction->priceTickerCurrency);
			$sellValueDefaultCurrency = $match->usedUnitsWithSplits->mul($transaction->priceDefaultCurrency);

			$buyValue = $match->usedOriginalUnits->mul($match->buy->priceTickerCurrency);
			$buyValueDefaultCurrency = $match->usedOriginalUnits->mul($match->buy->priceDefaultCurrency);

			$transactionRealizedGain = $transactionRealizedGain->add($sellValue->sub($buyValue));
			$transactionRealizedGainDefaultCurrency = $transactionRealizedGainDefaultCurrency->add(
				$sellValueDefaultCurrency->sub($buyValueDefaultCurrency),
			);
		}

		return new ValueDto(value: $transactionRealizedGain, valueDefaultCurrency: $transactionRealizedGainDefaultCurrency);
	}
}
