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

	/** @return \Iterator<Asset> */
	public function getAssets(
		User $user,
		Portfolio $portfolio,
		?DateTimeImmutable $dateTime = null,
		?Group $group = null,
		?Country $country = null,
		?Sector $sector = null,
		?Industry $industry = null,
	): \Iterator
	{
		return $this->assetRepository->findAssets(
			$user->id,
			$portfolio->id,
			$dateTime,
			$group?->id,
			$country?->id,
			$sector?->id,
			$industry?->id,
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
			$user->id,
			$portfolio?->id,
			$dateTime,
			$group?->id,
			$country?->id,
			$sector?->id,
			$industry?->id,
		);
	}

	public function getAsset(User $user, int $assetId): ?Asset
	{
		return $this->assetRepository->findAsset($assetId, $user->id);
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
			tickerId: $ticker->id,
			userId: $user->id,
			portfolioId: $portfolio->id,
		);
		if ($asset !== null) {
			return $asset;
		}

		return $this->createAsset(user: $user, portfolio: $portfolio, ticker: $ticker, othersGroup: $othersGroup);
	}
}
