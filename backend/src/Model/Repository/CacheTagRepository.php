<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\CacheTag;
use FinGather\Service\Cache\CacheDriverEnum;

/** @extends ABulkQueryRepository<CacheTag> */
final class CacheTagRepository extends ABulkQueryRepository
{
	/** @return list<string> */
	public function findCacheTagKeys(
		CacheDriverEnum $driver,
		?int $userId = null,
		?int $portfolioId = null,
		?DateTimeImmutable $date = null,
	): array
	{
		$cacheTagSelect = $this->select()
			->where('driver', $driver->value);

		if ($userId !== null) {
			$cacheTagSelect->where('user_id', $userId);
		}

		if ($portfolioId !== null) {
			$cacheTagSelect->where('portfolio_id', $portfolioId);
		}

		if ($date !== null) {
			$cacheTagSelect->where('date', $date);
		}

		// @phpstan-ignore-next-line
		return array_map(fn($item) => $item['key'], $cacheTagSelect->fetchData(false));
	}

	public function deleteCacheTag(
		CacheDriverEnum $driver,
		?int $userId = null,
		?int $portfolioId = null,
		?DateTimeImmutable $date = null,
	): void
	{
		$deleteAssetData = $this->orm->getSource(CacheTag::class)
			->getDatabase()
			->delete('cache_tags')
			->where('driver', $driver->value);

		if ($userId !== null) {
			$deleteAssetData->where('user_id', $userId);
		}

		if ($portfolioId !== null) {
			$deleteAssetData->where('portfolio_id', $portfolioId);
		}

		if ($date !== null) {
			$deleteAssetData->where('date', '>=', $date);
		}

		$deleteAssetData->run();
	}
}
