<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Service\Update\TickerRelationsUpdater;

class AssetProvider
{
	public function __construct(
		private readonly AssetRepository $assetRepository,
		private readonly TickerRelationsUpdater $tickerRelationsUpdater,
	)
	{
	}

	/** @return list<Asset> */
	public function getAssets(
		User $user,
		Portfolio $portfolio,
		?DateTimeImmutable $dateTime = null,
		?Group $group = null,
		?Country $country = null,
		?Sector $sector = null,
		?Industry $industry = null,
	): array
	{
		return $this->assetRepository->findAssets(
			$user->getId(),
			$portfolio->getId(),
			$dateTime,
			$group?->getId(),
			$country?->getId(),
			$sector?->getId(),
			$industry?->getId(),
		);
	}

	public function countAssets(
		User $user,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $dateTime = null,
		?Group $group = null,
		?Country $country = null,
		?Sector $sector = null,
		?Industry $industry = null,
	): int
	{
		return $this->assetRepository->countAssets(
			$user->getId(),
			$portfolio?->getId(),
			$dateTime,
			$group?->getId(),
			$country?->getId(),
			$sector?->getId(),
			$industry?->getId(),
		);
	}

	public function getAsset(User $user, int $assetId): ?Asset
	{
		return $this->assetRepository->findAsset($assetId, $user->getId());
	}

	public function createAsset(User $user, Portfolio $portfolio, Ticker $ticker, Group $othersGroup): Asset
	{
		$this->tickerRelationsUpdater->checkAndUpdateTicker($ticker);

		$asset = new Asset(user: $user, portfolio: $portfolio, ticker: $ticker, group: $othersGroup);

		$this->assetRepository->persist($asset);

		return $asset;
	}

	public function getOrCreateAsset(User $user, Portfolio $portfolio, Ticker $ticker, Group $othersGroup): Asset
	{
		$asset = $this->assetRepository->findAssetByTickerId(
			tickerId: $ticker->getId(),
			userId: $user->getId(),
			portfolioId: $portfolio->getId(),
		);
		if ($asset !== null) {
			return $asset;
		}

		return $this->createAsset(user: $user, portfolio: $portfolio, ticker: $ticker, othersGroup: $othersGroup);
	}
}
