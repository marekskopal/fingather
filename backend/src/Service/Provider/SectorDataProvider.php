<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\CacheDriverEnum;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheWithTags;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\DateTimeUtils;

class SectorDataProvider
{
	private CacheWithTags $cache;

	public function __construct(private readonly CalculatedGroupDataProvider $calculatedDataProvider, CacheFactory $cacheFactory)
	{
		$this->cache = $cacheFactory->create(driver: CacheDriverEnum::Redis, namespace: self::class);
	}

	public function getSectorData(Sector $sector, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$key = $sector->getId() . '-' . $portfolio->getId() . '-' . $dateTime->getTimestamp();

		/** @var CalculatedDataDto|null $sectorData */
		$sectorData = $this->cache->get($key);
		if ($sectorData !== null) {
			return $sectorData;
		}

		$calculatedData = $this->calculatedDataProvider->getCalculatedData($user, $portfolio, $dateTime, sector: $sector);

		$this->cache->setWithTags(
			key: $key,
			value: $calculatedData,
			userId: $user->getId(),
			portfolioId: $portfolio->getId(),
			date: $dateTime,
		);

		return $calculatedData;
	}

	public function deleteUserSectorData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->cache->deleteWithTags(
			$user?->getId(),
			$portfolio?->getId(),
			$date,
		);
	}
}
