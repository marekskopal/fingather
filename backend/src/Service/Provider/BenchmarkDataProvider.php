<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\CacheDriverEnum;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheWithTags;
use FinGather\Service\DataCalculator\BenchmarkDataCalculator;
use FinGather\Service\DataCalculator\Dto\BenchmarkDataDto;

class BenchmarkDataProvider
{
	private CacheWithTags $cache;

	public function __construct(
		private readonly BenchmarkDataCalculator $benchmarkDataCalculator,
		private readonly ExchangeRateProvider $exchangeRateProvider,
		private readonly TickerDataProvider $tickerDataProvider,
		CacheFactory $cacheFactory,
	) {
		$this->cache = $cacheFactory->create(driver: CacheDriverEnum::Redis, namespace: self::class);
	}

	/** @param list<Transaction> $transactions */
	public function getBenchmarkData(
		User $user,
		Portfolio $portfolio,
		Asset $benchmarkAsset,
		array $transactions,
		DateTimeImmutable $dateTime,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $benchmarkFromDateUnits,
	): BenchmarkDataDto {
		$dateTime = $dateTime->setTime(0, 0);
		$benchmarkFromDateTime = $benchmarkFromDateTime->setTime(0, 0);

		$key = $portfolio->getId() . '-' . $benchmarkAsset->getId() . '-' . $dateTime->getTimestamp() . '-' . $benchmarkFromDateTime->getTimestamp();

		/** @var BenchmarkDataDto|null $benchmarkData */
		$benchmarkData = $this->cache->get($key);
		if ($benchmarkData !== null) {
			return $benchmarkData;
		}

		$benchmarkData = $this->benchmarkDataCalculator->calculate(
			$portfolio,
			$transactions,
			$benchmarkAsset,
			$dateTime,
			$benchmarkFromDateTime,
			$benchmarkFromDateUnits,
		);

		$this->cache->setWithTags(
			key: $key,
			value: $benchmarkData,
			userId: $user->getId(),
			portfolioId: $portfolio->getId(),
		);

		return $benchmarkData;
	}

	public function getBenchmarkDataFromDate(
		User $user,
		Portfolio $portfolio,
		Asset $benchmarkAsset,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $portfolioDataValue,
	): BenchmarkDataDto {
		$benchmarkFromDateTime = $benchmarkFromDateTime->setTime(0, 0);

		$key = $portfolio->getId() . '-' . $benchmarkAsset->getId() . '-' . $benchmarkFromDateTime->getTimestamp() . '-' . $benchmarkFromDateTime->getTimestamp();

		/** @var BenchmarkDataDto|null $benchmarkData */
		$benchmarkData = $this->cache->get($key);
		if ($benchmarkData !== null) {
			return $benchmarkData;
		}

		$benchmarkTickerCurrency = $benchmarkAsset->getTicker()->getCurrency();

		$benchmarkAssetTickerDataClose = $this->tickerDataProvider->getLastTickerDataClose(
			$benchmarkAsset->getTicker(),
			$benchmarkFromDateTime,
		);
		if ($benchmarkAssetTickerDataClose !== null) {
			$benchmarkExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
				$benchmarkFromDateTime,
				$benchmarkTickerCurrency,
				$portfolio->getCurrency(),
			);

			$benchmarkUnitPriceDefaultCurrency = $benchmarkAssetTickerDataClose->mul($benchmarkExchangeRateDefaultCurrency);

			$benchmarkUnits = $portfolioDataValue->div($benchmarkUnitPriceDefaultCurrency);
		} else {
			$benchmarkUnits = new Decimal(0);
		}

		$benchmarkData = new BenchmarkDataDto(value: $portfolioDataValue, units: $benchmarkUnits);

		$this->cache->setWithTags(
			key: $key,
			value: $benchmarkData,
			userId: $user->getId(),
			portfolioId: $portfolio->getId(),
		);

		return $benchmarkData;
	}

	public function deleteBenchmarkData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->cache->deleteWithTags(
			$user?->getId(),
			$portfolio?->getId(),
			$date,
		);
	}
}
