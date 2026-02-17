<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheStorageEnum;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\DateTimeUtils;

class GroupDataProvider
{
	private Cache $cache;

	private const string CacheNamespace = 'group-data';

	public function __construct(private readonly CalculatedGroupDataProvider $calculatedDataProvider, CacheFactory $cacheFactory)
	{
		$this->cache = $cacheFactory->create(driver: CacheStorageEnum::Redis, namespace: self::CacheNamespace);
	}

	public function getGroupData(Group $group, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$key = $group->id . '-' . $portfolio->id . '-' . $dateTime->getTimestamp();

		/** @var CalculatedDataDto|null $groupData */
		$groupData = $this->cache->load($key);
		if ($groupData !== null) {
			return $groupData;
		}

		$calculatedData = $this->calculatedDataProvider->getCalculatedData($user, $portfolio, $dateTime, $group);

		$this->cache->save(key: $key, data: $calculatedData, user: $user, portfolio: $portfolio, date: $dateTime);

		return $calculatedData;
	}

	public function deleteUserGroupData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$date = $date !== null ? DateTimeUtils::setEndOfDateTime($date) : null;

		$this->cache->clean($user, $portfolio, $date);
	}
}
