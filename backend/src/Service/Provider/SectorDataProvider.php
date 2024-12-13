<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheStorageEnum;
use FinGather\Service\Cache\CacheTagEnum;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\DateTimeUtils;
use Nette\Caching\Cache;

class SectorDataProvider
{
	private Cache $cache;

	public function __construct(private readonly CalculatedGroupDataProvider $calculatedDataProvider, CacheFactory $cacheFactory)
	{
		$this->cache = $cacheFactory->create(driver: CacheStorageEnum::Redis, namespace: self::class);
	}

	public function getSectorData(Sector $sector, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$key = $sector->id . '-' . $portfolio->id . '-' . $dateTime->getTimestamp();

		/** @var CalculatedDataDto|null $sectorData */
		$sectorData = $this->cache->load($key);
		if ($sectorData !== null) {
			return $sectorData;
		}

		$calculatedData = $this->calculatedDataProvider->getCalculatedData($user, $portfolio, $dateTime, sector: $sector);

		$this->cache->save(
			key: $key,
			data: $calculatedData,
			dependencies: CacheTagEnum::getCacheTags($user, $portfolio, $dateTime),
		);

		return $calculatedData;
	}

	public function deleteUserSectorData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$date = $date !== null ? DateTimeUtils::setEndOfDateTime($date) : null;

		$this->cache->clean(
			CacheTagEnum::getCacheTags($user, $portfolio, $date),
		);
	}
}
