<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\BenchmarkData;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\BenchmarkDataRepository;
use Safe\DateTimeImmutable;

class BenchmarkDataProvider
{
	public function __construct(
		private readonly BenchmarkDataRepository $benchmarkDataRepository,
		private readonly AssetProvider $assetProvider,
		private readonly TransactionProvider $transactionProvider,
		private readonly ExchangeRateProvider $exchangeRateProvider,
		private readonly TickerDataProvider $tickerDataProvider,
	) {
	}

	public function getBenchmarkData(
		User $user,
		Portfolio $portfolio,
		Asset $benchmarkAsset,
		DateTimeImmutable $dateTime,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $benchmarkFromDateUnits,
	): BenchmarkData {
		$dateTime = $dateTime->setTime(0, 0);
		$benchmarkFromDateTime = $benchmarkFromDateTime->setTime(0, 0);

		$benchmarkData = $this->benchmarkDataRepository->findBenchmarkData(
			$user->getId(),
			$benchmarkAsset->getId(),
			$dateTime,
			$benchmarkFromDateTime,
		);
		if ($benchmarkData !== null) {
			return $benchmarkData;
		}

		$benchmarkTickerCurrency = $benchmarkAsset->getTicker()->getCurrency();

		$benchmarkUnitsSum = new Decimal(0);

		$assets = $this->assetProvider->getOpenAssets($user, $portfolio, $dateTime);
		foreach ($assets as $asset) {
			$transactions = $this->transactionProvider->getTransactions($user, $portfolio, $asset, $dateTime);
			if (count($transactions) === 0) {
				continue;
			}

			$tickerCurrency = $asset->getTicker()->getCurrency();

			foreach ($transactions as $transaction) {
				if (TransactionActionTypeEnum::from($transaction->getActionType()) === TransactionActionTypeEnum::Dividend) {
					continue;
				}

				$transactionActionCreated = DateTimeImmutable::createFromRegular($transaction->getActionCreated());
				if ($transactionActionCreated <= $benchmarkFromDateTime) {
					continue;
				}

				$transactionUnits = new Decimal($transaction->getUnits());
				$transactionPriceUnit = new Decimal($transaction->getPrice());

				if ($tickerCurrency->getId() !== $transaction->getCurrency()->getId()) {
					$transactionExchangeRate = $this->exchangeRateProvider->getExchangeRate(
						$transactionActionCreated,
						$transaction->getCurrency(),
						$tickerCurrency,
					);

					$transactionPriceUnit = $transactionPriceUnit->mul($transactionExchangeRate->getRate());
				}

				$transactionExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
					$transactionActionCreated,
					$tickerCurrency,
					$user->getDefaultCurrency(),
				);

				$transactionPriceUnitDefaultCurrency = $transactionPriceUnit->mul($transactionExchangeRateDefaultCurrency->getRate());

				$benchmarkTransactionExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
					$transactionActionCreated,
					$benchmarkTickerCurrency,
					$user->getDefaultCurrency(),
				);

				$benchmarkTransactionAssetTickerData = $this->tickerDataProvider->getLastTickerData(
					$benchmarkAsset->getTicker(),
					$transactionActionCreated,
				);
				if ($benchmarkTransactionAssetTickerData !== null) {
					$benchmarkPrice = new Decimal($benchmarkTransactionAssetTickerData->getClose());
					$benchmarkPriceUnitDefaultCurrency = $benchmarkPrice->mul($benchmarkTransactionExchangeRateDefaultCurrency->getRate());

					$benchmarkUnits = $transactionUnits->mul($transactionPriceUnitDefaultCurrency)->div($benchmarkPriceUnitDefaultCurrency);
				} else {
					$benchmarkUnits = new Decimal(0);
				}

				$benchmarkUnitsSum = $benchmarkUnitsSum->add($benchmarkUnits);
			}
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
				(new Decimal($benchmarkAssetTickerData->getClose()))->mul($benchmarkExchangeRateDefaultCurrency->getRate()),
			);
		} else {
			$value = new Decimal(0);
		}

		$benchmarkData = new BenchmarkData(
			user: $user,
			portfolio: $portfolio,
			asset: $benchmarkAsset,
			date: $dateTime,
			fromDate: $benchmarkFromDateTime,
			value: (string) $value,
			units: (string) $benchmarkUnitsSum,
		);

		$this->benchmarkDataRepository->persist($benchmarkData);

		return $benchmarkData;
	}

	public function getBenchmarkDataFromDate(
		User $user,
		Portfolio $portfolio,
		Asset $benchmarkAsset,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $portfolioDataValue,
	): BenchmarkData
	{
		$benchmarkFromDateTime = $benchmarkFromDateTime->setTime(0, 0);

		$benchmarkData = $this->benchmarkDataRepository->findBenchmarkData(
			$user->getId(),
			$benchmarkAsset->getId(),
			$benchmarkFromDateTime,
			$benchmarkFromDateTime,
		);
		if ($benchmarkData !== null) {
			return $benchmarkData;
		}

		$benchmarkTickerCurrency = $benchmarkAsset->getTicker()->getCurrency();

		$benchmarkAssetTickerData = $this->tickerDataProvider->getLastTickerData($benchmarkAsset->getTicker(), $benchmarkFromDateTime);
		if ($benchmarkAssetTickerData !== null) {
			$benchmarkExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
				$benchmarkFromDateTime,
				$benchmarkTickerCurrency,
				$user->getDefaultCurrency(),
			);

			$benchmarkUnitPriceDefaultCurrency = (new Decimal($benchmarkAssetTickerData->getClose()))->mul(
				$benchmarkExchangeRateDefaultCurrency->getRate(),
			);

			$benchmarkUnits = $portfolioDataValue->div($benchmarkUnitPriceDefaultCurrency);
		} else {
			$benchmarkUnits = new Decimal(0);
		}

		$benchmarkData = new BenchmarkData(
			user: $user,
			portfolio: $portfolio,
			asset: $benchmarkAsset,
			date: $benchmarkFromDateTime,
			fromDate: $benchmarkFromDateTime,
			value: (string) $portfolioDataValue,
			units: (string) $benchmarkUnits,
		);

		$this->benchmarkDataRepository->persist($benchmarkData);

		return $benchmarkData;
	}

	public function deleteBenchmarkData(User $user, Portfolio $portfolio, DateTimeImmutable $date): void
	{
		$this->benchmarkDataRepository->deleteBenchmarkData($user->getId(), $portfolio->getId(), $date);
	}
}
