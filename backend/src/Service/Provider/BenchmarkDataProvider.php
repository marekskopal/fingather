<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheStorageEnum;
use FinGather\Service\Cache\CacheTagEnum;
use FinGather\Service\DataCalculator\BenchmarkDataCalculator;
use FinGather\Service\DataCalculator\Dto\BenchmarkDataDto;
use FinGather\Utils\DateTimeUtils;
use Nette\Caching\Cache;

class BenchmarkDataProvider
{
	private Cache $cache;

	public function __construct(
		private readonly BenchmarkDataCalculator $benchmarkDataCalculator,
		private readonly ExchangeRateProvider $exchangeRateProvider,
		private readonly TickerDataProvider $tickerDataProvider,
		CacheFactory $cacheFactory,
	) {
		$this->cache = $cacheFactory->create(driver: CacheStorageEnum::Redis, namespace: self::class);
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
		$dateTime = DateTimeUtils::setStartOfDateTime($dateTime);
		$benchmarkFromDateTime = DateTimeUtils::setStartOfDateTime($benchmarkFromDateTime);

		$key = $portfolio->id . '-' . $benchmarkAsset->id . '-' . $dateTime->getTimestamp() . '-' . $benchmarkFromDateTime->getTimestamp();

		/** @var BenchmarkDataDto|null $benchmarkData */
		$benchmarkData = $this->cache->load($key);
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

		$this->cache->save(
			key: $key,
			data: $benchmarkData,
			dependencies: CacheTagEnum::getCacheTags($user, $portfolio),
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
		$benchmarkFromDateTime = DateTimeUtils::setStartOfDateTime($benchmarkFromDateTime);

		$key = $portfolio->id . '-' . $benchmarkAsset->id . '-' . $benchmarkFromDateTime->getTimestamp() . '-' . $benchmarkFromDateTime->getTimestamp();

		/** @var BenchmarkDataDto|null $benchmarkData */
		$benchmarkData = $this->cache->load($key);
		if ($benchmarkData !== null) {
			return $benchmarkData;
		}

		$benchmarkTickerCurrency = $benchmarkAsset->ticker->getCurrency();

		$benchmarkAssetTickerDataClose = $this->tickerDataProvider->getLastTickerDataClose(
			$benchmarkAsset->ticker,
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

		$this->cache->save(
			key: $key,
			data: $benchmarkData,
			dependencies: CacheTagEnum::getCacheTags($user, $portfolio),
		);

		return $benchmarkData;
	}

	public function deleteBenchmarkData(?User $user = null, ?Portfolio $portfolio = null): void
	{
		$this->cache->clean(
			CacheTagEnum::getCacheTags($user, $portfolio),
		);
	}
}
