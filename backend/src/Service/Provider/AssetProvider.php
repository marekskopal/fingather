<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;

class AssetProvider
{
	public function __construct(private readonly AssetRepository $assetRepository, private readonly GroupProvider $groupProvider,)
	{
	}

	/** @return array<int, Asset> */
	public function getAssets(
		User $user,
		Portfolio $portfolio,
		?DateTimeImmutable $dateTime = null,
		?Group $group = null,
		?Country $country = null,
	): array
	{
		return iterator_to_array(
			$this->assetRepository->findAssets($user->getId(), $portfolio->getId(), $dateTime, $group?->getId(), $country?->getId()),
		);
	}

	public function countAssets(
		User $user,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $dateTime = null,
		?Group $group = null,
		?Country $country = null,
	): int
	{
		return $this->assetRepository->countAssets($user->getId(), $portfolio?->getId(), $dateTime, $group?->getId(), $country?->getId());
	}

	public function getAsset(User $user, int $assetId): ?Asset
	{
		return $this->assetRepository->findAsset($assetId, $user->getId());
	}

	public function createAsset(User $user, Portfolio $portfolio, Ticker $ticker): Asset
	{
		$group = $this->groupProvider->getOthersGroup($user, $portfolio);

		$asset = new Asset(user: $user, portfolio: $portfolio, ticker: $ticker, group: $group);

		$this->assetRepository->persist($asset);

		return $asset;
	}

	public function getOrCreateAsset(User $user, Portfolio $portfolio, Ticker $ticker, Group $othersGroup): Asset
	{
		$asset = $this->assetRepository->findAssetByTickerId($user->getId(), $portfolio->getId(), $ticker->getId());
		if ($asset !== null) {
			return $asset;
		}

		$asset = new Asset(user: $user, portfolio: $portfolio, ticker: $ticker, group: $othersGroup);
		$this->assetRepository->persist($asset);

		return $asset;
	}
}
