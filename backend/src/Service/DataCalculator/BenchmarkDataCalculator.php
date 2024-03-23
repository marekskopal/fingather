<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\BenchmarkDataDto;
use FinGather\Service\Provider\ExchangeRateProvider;
use FinGather\Service\Provider\TickerDataProvider;

class BenchmarkDataCalculator
{
	/** @var array<string, Decimal> */
	private array $transactionBenchmarkUnits = [];

	public function __construct(
		private readonly ExchangeRateProvider $exchangeRateProvider,
		private readonly TickerDataProvider $tickerDataProvider,
	) {
	}

	/** @param list<Transaction> $transactions */
	public function calculate(
		User $user,
		array $transactions,
		Asset $benchmarkAsset,
		DateTimeImmutable $dateTime,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $benchmarkFromDateUnits,
	): BenchmarkDataDto {
		$benchmarkTickerCurrency = $benchmarkAsset->getTicker()->getCurrency();
		$defaultCurrency = $user->getDefaultCurrency();

		$benchmarkUnitsSum = new Decimal(0);

		foreach ($transactions as $transaction) {
			if ($transaction->getActionCreated() < $benchmarkFromDateTime) {
				continue;
			}

			if ($transaction->getActionCreated() > $dateTime) {
				break;
			}

			$benchmarkUnitsSum = $benchmarkUnitsSum->add($this->calculateTransactionBenchmarkUnits(
				$transaction,
				$benchmarkAsset->getTicker(),
				$benchmarkTickerCurrency,
				$defaultCurrency,
			));
		}

		$benchmarkAssetTickerData = $this->tickerDataProvider->getLastTickerData($benchmarkAsset->getTicker(), $dateTime);
		if ($benchmarkAssetTickerData !== null) {
			$benchmarkExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
				$dateTime,
				$benchmarkTickerCurrency,
				$user->getDefaultCurrency(),
			);

			$benchmarkUnitsSum = $benchmarkUnitsSum->add($benchmarkFromDateUnits);

			$value = $benchmarkUnitsSum->mul(
				$benchmarkAssetTickerData->getClose()->mul($benchmarkExchangeRateDefaultCurrency),
			);
		} else {
			$value = new Decimal(0);
		}

		return new BenchmarkDataDto(value: $value, units: $benchmarkUnitsSum);
	}

	private function calculateTransactionBenchmarkUnits(
		Transaction $transaction,
		Ticker $benchmarkAssetTicker,
		Currency $benchmarkTickerCurrency,
		Currency $defaultCurrency,
	): Decimal {
		$key = $transaction->getId() . '-' . $benchmarkAssetTicker->getId() . '-' . $defaultCurrency->getId();
		if (isset($this->transactionBenchmarkUnits[$key])) {
			return $this->transactionBenchmarkUnits[$key];
		}

		$transactionActionCreated = $transaction->getActionCreated();

		$benchmarkTransactionAssetTickerData = $this->tickerDataProvider->getLastTickerData(
			$benchmarkAssetTicker,
			$transactionActionCreated,
		);
		if ($benchmarkTransactionAssetTickerData === null) {
			return new Decimal(0);
		}

		$transactionUnits = $transaction->getUnits();

		$transactionPriceUnitDefaultCurrency = $transaction->getPriceDefaultCurrency();

		$benchmarkTransactionExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
			$transactionActionCreated,
			$benchmarkTickerCurrency,
			$defaultCurrency,
		);

		$benchmarkPrice = $benchmarkTransactionAssetTickerData->getClose();
		$benchmarkPriceUnitDefaultCurrency = $benchmarkPrice->mul($benchmarkTransactionExchangeRateDefaultCurrency);

		$this->transactionBenchmarkUnits[$key] = $transactionUnits->mul($transactionPriceUnitDefaultCurrency)->div(
			$benchmarkPriceUnitDefaultCurrency,
		);

		return $this->transactionBenchmarkUnits[$key];
	}
}
