<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Service\DataCalculator\AssetDataCalculator;
use FinGather\Service\Provider\Dto\AssetPropertiesDto;
use Safe\DateTimeImmutable;

class AssetProvider
{
	public function __construct(
		private readonly AssetRepository $assetRepository,
		private readonly GroupProvider $groupProvider,
		private readonly AssetDataCalculator $assetDataCalculator,
	) {
	}

	/** @return array<int, Asset> */
	public function getAssets(User $user, Portfolio $portfolio, ?DateTimeImmutable $dateTime = null, ?Group $group = null): array
	{
		return $this->assetRepository->findAssets($user->getId(), $portfolio->getId(), $dateTime, $group?->getId());
	}

	public function countAssets(User $user, ?Portfolio $portfolio = null, ?DateTimeImmutable $dateTime = null, ?int $groupId = null): int
	{
		return $this->assetRepository->countAssets($user->getId(), $portfolio?->getId(), $dateTime, $groupId);
	}

	public function getAsset(User $user, int $assetId): ?Asset
	{
		return $this->assetRepository->findAsset($assetId, $user->getId());
	}

	public function getAssetProperties(User $user, Portfolio $portfolio, Asset $asset, DateTimeImmutable $dateTime): ?AssetPropertiesDto
	{
		return $this->assetDataCalculator->calculate($user, $portfolio, $asset, $dateTime);
	}

	public function createAsset(User $user, Portfolio $portfolio, Ticker $ticker): Asset
	{
		$group = $this->groupProvider->getOthersGroup($user, $portfolio);

		$asset = new Asset(
			user: $user,
			portfolio: $portfolio,
			ticker: $ticker,
			group: $group,
			transactions: [],
		);

		$this->assetRepository->persist($asset);

		return $asset;
	}
}
