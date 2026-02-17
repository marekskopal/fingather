<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Cache\CacheStorageEnum;
use FinGather\Service\DataCalculator\DataCalculator;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\DateTimeUtils;
use Psr\Log\LoggerInterface;

class PortfolioDataProvider
{
	private Cache $cache;

	private const string CacheNamespace = 'portfolio-data';

	public function __construct(
		private readonly DataCalculator $dataCalculator,
		private readonly AssetProvider $assetProvider,
		private readonly AssetDataProvider $assetDataProvider,
		private readonly TransactionProvider $transactionProvider,
		private readonly LoggerInterface $logger,
		CacheFactory $cacheFactory,
	) {
		$this->cache = $cacheFactory->create(driver: CacheStorageEnum::Redis, namespace: self::CacheNamespace);
	}

	public function getPortfolioData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$key = $portfolio->id . '-' . $dateTime->getTimestamp();

		/** @var CalculatedDataDto|null $portfolioData */
		$portfolioData = $this->cache->load($key);
		if ($portfolioData !== null) {
			return $portfolioData;
		}

		$this->logger->debug(
			'Calculating portfolio data for user ' . $user->id . ' and portfolio ' . $portfolio->id . ' and date ' . $dateTime->format(
				'Y-m-d',
			),
		);

		$assetDatas = [];

		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null) {
				continue;
			}

			$assetDatas[] = $assetData;
		}

		$fistTransactionActionCreated = $this->transactionProvider->getFirstTransaction($user, $portfolio)->actionCreated ?? $dateTime;

		$calculatedData = $this->dataCalculator->calculate($assetDatas, $dateTime, $fistTransactionActionCreated);

		$this->cache->save(key: $key, data: $calculatedData, user: $user, portfolio: $portfolio, date: $dateTime);

		return $calculatedData;
	}

	public function deletePortfolioData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$date = $date !== null ? DateTimeUtils::setEndOfDateTime($date) : null;

		$this->cache->clean(user: $user, portfolio: $portfolio, date: $date);
	}
}
