<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\CacheDriverEnum;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheWithTags;
use FinGather\Service\DataCalculator\AssetDataCalculator;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Utils\DateTimeUtils;

class AssetDataProvider
{
	private CacheWithTags $cache;

	public function __construct(private readonly AssetDataCalculator $assetDataCalculator, CacheFactory $cacheFactory,)
	{
		$this->cache = $cacheFactory->create(driver: CacheDriverEnum::Redis, namespace: self::class);
	}

	public function getAssetData(User $user, Portfolio $portfolio, Asset $asset, DateTimeImmutable $dateTime): ?AssetDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$key = $asset->getId() . $dateTime->getTimestamp();

		/** @var AssetDataDto|null $assetData */
		$assetData = $this->cache->get($key);
		if ($assetData !== null) {
			return $assetData;
		}

		$assetDataDto = $this->assetDataCalculator->calculate($user, $portfolio, $asset, $dateTime);
		if ($assetDataDto === null) {
			return null;
		}

		$this->cache->setWithTags(
			key: $key,
			value: $assetDataDto,
			userId: $user->getId(),
			portfolioId: $portfolio->getId(),
			date: $dateTime,
		);

		return $assetData;
	}

	public function deleteAssetData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->cache->deleteWithTags(
			$user?->getId(),
			$portfolio?->getId(),
			$date,
		);
	}
}
