<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheStorageEnum;
use FinGather\Service\Cache\CacheTagEnum;
use FinGather\Service\DataCalculator\AssetDataCalculator;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Utils\DateTimeUtils;
use Nette\Caching\Cache;

class AssetDataProvider
{
	private Cache $cache;

	public function __construct(private readonly AssetDataCalculator $assetDataCalculator, CacheFactory $cacheFactory,)
	{
		$this->cache = $cacheFactory->create(driver: CacheStorageEnum::Redis, namespace: self::class);
	}

	public function getAssetData(User $user, Portfolio $portfolio, Asset $asset, DateTimeImmutable $dateTime): ?AssetDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$key = $asset->getId() . '-' . $dateTime->getTimestamp();

		/** @var AssetDataDto|null $assetData */
		$assetData = $this->cache->load($key);
		if ($assetData !== null) {
			return $assetData;
		}

		$assetData = $this->assetDataCalculator->calculate($user, $portfolio, $asset, $dateTime);
		if ($assetData === null) {
			return null;
		}

		$this->cache->save(
			key: $key,
			data: $assetData,
			dependencies: CacheTagEnum::getCacheTags($user, $portfolio, $dateTime),
		);

		return $assetData;
	}

	public function deleteAssetData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$date = $date !== null ? DateTimeUtils::setEndOfDateTime($date) : null;

		$this->cache->clean(
			CacheTagEnum::getCacheTags($user, $portfolio, $date),
		);
	}
}
