<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\GroupDataDto;
use FinGather\Dto\GroupWithGroupDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Utils\CalculatorUtils;
use Safe\DateTimeImmutable;

class GroupWithGroupDataProvider
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly AssetProvider $assetProvider,
		private readonly GroupDataProvider $groupDataProvider,
	) {
	}

	/** @return list<GroupWithGroupDataDto> */
	public function getGroupsWithGroupData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);

		$groups = [];
		$groupAssets = [];

		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$assetProperties = $this->assetProvider->getAssetProperties($user, $portfolio, $asset, $dateTime);
			if ($assetProperties === null || $assetProperties->isClosed()) {
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
			$groupData = $this->groupDataProvider->getGroupData($group, $user, $portfolio, $dateTime);

			$groupsWithGroupData[] = new GroupWithGroupDataDto(
				id: $groupId,
				userId: $user->getId(),
				name: $group->getName(),
				color: $group->getColor(),
				assetIds: array_map(fn (AssetWithPropertiesDto $asset): int => $asset->id, $groupAssets[$groupId]),
				assets: $groupAssets[$groupId],
				percentage: CalculatorUtils::toPercentage($groupData->getValue(), $portfolioData->getValue()),
				groupData: GroupDataDto::fromEntity($groupData),
			);
		}

		return $groupsWithGroupData;
	}
}
