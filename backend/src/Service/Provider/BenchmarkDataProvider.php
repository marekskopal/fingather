<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\BenchmarkData;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\TickerData;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\BenchmarkDataRepository;
use FinGather\Model\Repository\SplitRepository;
use Safe\DateTimeImmutable;

class BenchmarkDataProvider
{
	public function __construct(
		private readonly BenchmarkDataRepository $benchmarkDataRepository,
		private readonly AssetProvider $assetProvider,
		private readonly TransactionProvider $transactionProvider,
		private readonly SplitRepository $splitRepository,
		private readonly ExchangeRateProvider $exchangeRateProvider,
		private readonly TickerDataProvider $tickerDataProvider,
	) {
	}

	public function getBenchmarkData(User $user, Asset $benchmarkAsset, DateTimeImmutable $dateTime): BenchmarkData
	{
		$dateTime = $dateTime->setTime(0, 0);

		$benchmarkData = $this->benchmarkDataRepository->findBenchmarkData($user->getId(), $benchmarkAsset->getId(), $dateTime);
		if ($benchmarkData !== null) {
			return $benchmarkData;
		}

		$benchmarkTickerCurrency = $benchmarkAsset->getTicker()->getCurrency();

		$benchmarkSplits = $this->splitRepository->findSplits($benchmarkAsset->getTicker()->getId());

		$benchmarkUnitsSum = new Decimal(0);

		$assets = $this->assetProvider->getOpenAssets($user, $dateTime);
		foreach ($assets as $asset) {
			$transactions = $this->transactionProvider->getTransactions($user, $asset, $dateTime);
			if (count($transactions) === 0) {
				continue;
			}

			$splits = $this->splitRepository->findSplits($asset->getTicker()->getId());

			$tickerCurrency = $asset->getTicker()->getCurrency();

			foreach ($transactions as $transaction) {
				if (TransactionActionTypeEnum::from($transaction->getActionType()) === TransactionActionTypeEnum::Dividend) {
					continue;
				}

				$splitFactor = new Decimal(1);

				foreach ($splits as $split) {
					if ($split->getDate() >= $transaction->getActionCreated() && $split->getDate() <= $dateTime) {
						$splitFactor = $splitFactor->mul(new Decimal($split->getFactor()));
					}
				}

				$transactionUnits = (new Decimal($transaction->getUnits()))->mul($splitFactor);
				$transactionPriceUnit = (new Decimal($transaction->getPrice()))->div($splitFactor);

				$transactionActionCreated = DateTimeImmutable::createFromRegular($transaction->getActionCreated());

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

				$benchmarkSplitFactor = new Decimal(1);

				foreach ($benchmarkSplits as $benchmarkSplit) {
					if ($benchmarkSplit->getDate() >= $transaction->getActionCreated() && $benchmarkSplit->getDate() <= $dateTime) {
						$splitFactor = $splitFactor->mul(new Decimal($benchmarkSplit->getFactor()));
					}
				}

				$benchmarkTransactionAssetTickerData = $this->tickerDataProvider->getLastTickerData(
					$benchmarkAsset->getTicker(),
					$transactionActionCreated,
				);
				assert($benchmarkTransactionAssetTickerData instanceof TickerData);

				$benchmarkPrice = (new Decimal($benchmarkTransactionAssetTickerData->getClose()))->div($benchmarkSplitFactor);
				$benchmarkPriceUnitDefaultCurrency = $benchmarkPrice->mul($benchmarkTransactionExchangeRateDefaultCurrency->getRate());

				$benchmarkUnits = $transactionUnits->mul($transactionPriceUnitDefaultCurrency)->div($benchmarkPriceUnitDefaultCurrency);

				$benchmarkUnitsSum = $benchmarkUnitsSum->add($benchmarkUnits);
			}
		}

		$benchmarkAssetTickerData = $this->tickerDataProvider->getLastTickerData($benchmarkAsset->getTicker(), $dateTime);
		assert($benchmarkAssetTickerData instanceof TickerData);

		$benchmarkExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
			$dateTime,
			$benchmarkTickerCurrency,
			$user->getDefaultCurrency(),
		);

		$value = $benchmarkUnitsSum->mul(
			(new Decimal($benchmarkAssetTickerData->getClose()))->mul($benchmarkExchangeRateDefaultCurrency->getRate()),
		);

		$benchmarkData = new BenchmarkData(user: $user, asset: $benchmarkAsset, date: $dateTime, value: (string) $value);

		$this->benchmarkDataRepository->persist($benchmarkData);

		return $benchmarkData;
	}

	public function deleteBenchmarkData(User $user, DateTimeImmutable $date): void
	{
		$this->benchmarkDataRepository->deleteBenchmarkData($user->getId(), $date);
	}
}
