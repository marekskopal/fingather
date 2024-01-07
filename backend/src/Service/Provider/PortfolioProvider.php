<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Decimal\Decimal;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\GroupDataDto;
use FinGather\Dto\GroupWithGroupDataDto;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Dto\PortfolioDto;
use FinGather\Model\Entity\User;
use Safe\DateTimeImmutable;

class PortfolioProvider
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly AssetProvider $assetProvider,
		private readonly GroupDataProvider $groupDataProvider,
	) {
	}

	public function getPortfolio(User $user, DateTimeImmutable $dateTime): PortfolioDto
	{
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $dateTime);

		$groups = [];
		$groupAssets = [];

		$assets = $this->assetProvider->getOpenAssets($user, $dateTime);
		foreach ($assets as $asset) {
			$assetProperties = $this->assetProvider->getAssetProperties($user, $asset, $dateTime);
			if ($assetProperties === null) {
				continue;
			}

			$assetDto = AssetWithPropertiesDto::fromEntity($asset, $assetProperties);

			$group = $asset->getGroup();

			$groupAssets[$group->getId()][] = $assetDto;

			if (array_key_exists($group->getId(), $groups)) {
				continue;
			}

			$groups[$group->getId()] = $group;
		}

		$groupsWithGroupData = [];

		foreach	($groups as $groupId => $group) {
			$groupData = $this->groupDataProvider->getGroupData($group, $user, $dateTime);

			$groupsWithGroupData[] = new GroupWithGroupDataDto(
				id: $groupId,
				userId: $user->getId(),
				name: $group->getName(),
				assetIds: array_map(fn (AssetWithPropertiesDto $asset): int => $asset->id, $groupAssets[$groupId]),
				assets: $groupAssets[$groupId],
				percentage: ((new Decimal($groupData->getValue()))->div(new Decimal($portfolioData->getValue())))->toFloat() * 100,
				groupData: GroupDataDto::fromEntity($groupData),
			);
		}

		return new PortfolioDto($groupsWithGroupData, PortfolioDataDto::fromEntity($portfolioData));
	}
}
