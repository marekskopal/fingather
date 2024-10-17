<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheStorageEnum;
use FinGather\Service\Cache\CacheTagEnum;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\DateTimeUtils;
use Nette\Caching\Cache;

class IndustryDataProvider
{
	private Cache $cache;

	public function __construct(private readonly CalculatedGroupDataProvider $calculatedDataProvider, CacheFactory $cacheFactory)
	{
		$this->cache = $cacheFactory->create(driver: CacheStorageEnum::Redis, namespace: self::class);
	}

	public function getIndustryData(Industry $industry, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$key = $industry->getId() . '-' . $portfolio->getId() . '-' . $dateTime->getTimestamp();

		/** @var CalculatedDataDto|null $industryData */
		$industryData = $this->cache->load($key);
		if ($industryData !== null) {
			return $industryData;
		}

		$calculatedData = $this->calculatedDataProvider->getCalculatedData($user, $portfolio, $dateTime, industry: $industry);

		$this->cache->save(
			key: $key,
			data: $calculatedData,
			dependencies: CacheTagEnum::getCacheTags($user, $portfolio, $dateTime),
		);

		return $calculatedData;
	}

	public function deleteUserIndustryData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$date = $date !== null ? DateTimeUtils::setEndOfDateTime($date) : null;

		$this->cache->clean(
			CacheTagEnum::getCacheTags($user, $portfolio, $date),
		);
	}
}
