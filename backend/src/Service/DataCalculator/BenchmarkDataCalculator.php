<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Service\DataCalculator\Dto\BenchmarkDataDto;
use FinGather\Service\Provider\ExchangeRateProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;

final class BenchmarkDataCalculator
{
	/** @var array<string, Decimal> */
	private array $transactionBenchmarkUnits = [];

	public function __construct(
		private readonly ExchangeRateProviderInterface $exchangeRateProvider,
		private readonly TickerDataProviderInterface $tickerDataProvider,
	) {
	}

	/** @param list<Transaction> $transactions */
	public function calculate(
		Portfolio $portfolio,
		array $transactions,
		Ticker $benchmarkTicker,
		DateTimeImmutable $dateTime,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $benchmarkFromDateUnits,
	): BenchmarkDataDto {
		$benchmarkTickerCurrency = $benchmarkTicker->currency;
		$defaultCurrency = $portfolio->currency;

		$benchmarkUnitsSum = new Decimal(0);

		foreach ($transactions as $transaction) {
			if ($transaction->actionCreated < $benchmarkFromDateTime) {
				continue;
			}

			if ($transaction->actionCreated > $dateTime) {
				break;
			}

			$benchmarkUnitsSum = $benchmarkUnitsSum->add($this->calculateTransactionBenchmarkUnits(
				$transaction,
				$benchmarkTicker,
				$benchmarkTickerCurrency,
				$defaultCurrency,
			));
		}

		$benchmarkTickerDataClose = $this->tickerDataProvider->getLastTickerDataClose($benchmarkTicker, $dateTime);
		if ($benchmarkTickerDataClose !== null) {
			$benchmarkExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
				$dateTime,
				$benchmarkTickerCurrency,
				$defaultCurrency,
			);

			$benchmarkUnitsSum = $benchmarkUnitsSum->add($benchmarkFromDateUnits);

			$value = $benchmarkUnitsSum->mul(
				$benchmarkTickerDataClose->mul($benchmarkExchangeRateDefaultCurrency),
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
		$key = $transaction->id . '-' . $benchmarkAssetTicker->id . '-' . $defaultCurrency->id;
		if (isset($this->transactionBenchmarkUnits[$key])) {
			return $this->transactionBenchmarkUnits[$key];
		}

		$transactionActionCreated = $transaction->actionCreated;

		$benchmarkTransactionAssetTickerDataClose = $this->tickerDataProvider->getLastTickerDataClose(
			$benchmarkAssetTicker,
			$transactionActionCreated,
		);
		if ($benchmarkTransactionAssetTickerDataClose === null) {
			return new Decimal(0);
		}

		$transactionUnits = $transaction->units;

		$transactionPriceUnitDefaultCurrency = $transaction->priceDefaultCurrency;

		$benchmarkTransactionExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
			$transactionActionCreated,
			$benchmarkTickerCurrency,
			$defaultCurrency,
		);

		$benchmarkPrice = $benchmarkTransactionAssetTickerDataClose;
		$benchmarkPriceUnitDefaultCurrency = $benchmarkPrice->mul($benchmarkTransactionExchangeRateDefaultCurrency);

		$this->transactionBenchmarkUnits[$key] = $transactionUnits->mul($transactionPriceUnitDefaultCurrency)->div(
			$benchmarkPriceUnitDefaultCurrency,
		);

		return $this->transactionBenchmarkUnits[$key];
	}
}
