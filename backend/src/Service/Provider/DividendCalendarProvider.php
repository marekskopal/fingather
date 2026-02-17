<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\DividendCalendarItemDto;
use FinGather\Dto\TickerDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheStorageEnum;
use FinGather\Utils\DateTimeUtils;
use MarekSkopal\TwelveData\Dto\Fundamentals\DividendsCalendar;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;

final class DividendCalendarProvider
{
	private Cache $cache;

	private const string CacheNamespace = 'dividend-calendar';

	private const int CacheTtlSeconds = 86400;

	public function __construct(
		private readonly AssetProviderInterface $assetProvider,
		private readonly AssetDataProviderInterface $assetDataProvider,
		private readonly ExchangeRateProviderInterface $exchangeRateProvider,
		private readonly TwelveData $twelveData,
		CacheFactory $cacheFactory,
	) {
		$this->cache = $cacheFactory->create(driver: CacheStorageEnum::Redis, namespace: self::CacheNamespace);
	}

	/** @return list<DividendCalendarItemDto> */
	public function getDividendCalendar(User $user, Portfolio $portfolio): array
	{
		$today = new DateTimeImmutable('today');
		$endDate = new DateTimeImmutable('+12 months');

		$items = [];

		foreach ($this->assetProvider->getAssets($user, $portfolio) as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $today);
			if ($assetData === null || $assetData->isClosed()) {
				continue;
			}

			$ticker = $asset->ticker;
			$cacheKey = $ticker->ticker . '_' . $ticker->market->mic . '_' . $today->format('Y-m-d');

			/** @var list<DividendsCalendar>|null $calendarEntries */
			$calendarEntries = $this->cache->load($cacheKey);
			if ($calendarEntries === null) {
				try {
					$calendarEntries = $this->twelveData->fundamentals->dividendsCalendar(
						symbol: $ticker->ticker,
						micCode: $ticker->market->mic,
						startDate: $today,
						endDate: $endDate,
					);
				} catch (NotFoundException) {
					$calendarEntries = [];
				}

				$this->cache->save(key: $cacheKey, data: $calendarEntries, expireSeconds: self::CacheTtlSeconds);
			}

			$exchangeRate = $this->exchangeRateProvider->getExchangeRate($today, $ticker->currency, $portfolio->currency);

			foreach ($calendarEntries as $entry) {
				$amountPerShare = new Decimal((string) $entry->amount);
				$totalAmount = $amountPerShare->mul($assetData->units);
				$totalAmountDefaultCurrency = $totalAmount->mul($exchangeRate);

				$items[] = new DividendCalendarItemDto(
					assetId: $asset->id,
					ticker: TickerDto::fromEntity($ticker),
					exDate: DateTimeUtils::formatZulu($entry->exDate),
					amountPerShare: $amountPerShare,
					units: $assetData->units,
					totalAmount: $totalAmount,
					totalAmountDefaultCurrency: $totalAmountDefaultCurrency,
				);
			}
		}

		usort($items, static fn (DividendCalendarItemDto $a, DividendCalendarItemDto $b): int => $a->exDate <=> $b->exDate);

		return $items;
	}
}
