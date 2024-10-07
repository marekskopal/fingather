<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\CacheDriverEnum;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheWithTags;
use FinGather\Service\DataCalculator\DataCalculator;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\DateTimeUtils;

class PortfolioDataProvider
{
	private CacheWithTags $cache;

	public function __construct(
		private readonly DataCalculator $dataCalculator,
		private readonly AssetProvider $assetProvider,
		private readonly AssetDataProvider $assetDataProvider,
		private readonly TransactionProvider $transactionProvider,
		CacheFactory $cacheFactory,
	) {
		$this->cache = $cacheFactory->create(driver: CacheDriverEnum::Redis, namespace: self::class);
	}

	public function getPortfolioData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$key = $portfolio->getId() . '-' . $dateTime->getTimestamp();

		/** @var CalculatedDataDto|null $portfolioData */
		$portfolioData = $this->cache->get($key);
		if ($portfolioData !== null) {
			return $portfolioData;
		}

		$assetDatas = [];

		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null) {
				continue;
			}

			$assetDatas[] = $assetData;
		}

		$fistTransactionActionCreated = $this->transactionProvider->getFirstTransaction(
			$user,
			$portfolio,
		)?->getActionCreated() ?? $dateTime;

		$calculatedData = $this->dataCalculator->calculate($assetDatas, $dateTime, $fistTransactionActionCreated);

		$this->cache->setWithTags(
			key: $key,
			value: $calculatedData,
			userId: $user->getId(),
			portfolioId: $portfolio->getId(),
			date: $dateTime,
		);

		return $calculatedData;
	}

	public function deletePortfolioData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->cache->deleteWithTags(
			$user?->getId(),
			$portfolio?->getId(),
			$date,
		);
	}
}
