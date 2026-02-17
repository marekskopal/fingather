<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\GroupDataDto;
use FinGather\Dto\GroupWithGroupDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Utils\CalculatorUtils;

final readonly class GroupWithGroupDataProvider
{
	public function __construct(
		private PortfolioDataProvider $portfolioDataProvider,
		private AssetProviderInterface $assetProvider,
		private GroupDataProvider $groupDataProvider,
		private AssetDataProviderInterface $assetDataProvider,
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
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null || $assetData->isClosed()) {
				continue;
			}

			$assetDto = AssetWithPropertiesDto::fromEntity(
				$asset,
				$assetData,
				CalculatorUtils::toPercentage($assetData->value, $portfolioData->value),
			);

			$group = $asset->group;

			$groupAssets[$group->id][] = $assetDto;

			if (array_key_exists($group->id, $groups)) {
				continue;
			}

			$groups[$group->id] = $group;
		}

		$groupsWithGroupData = [];

		foreach	($groups as $groupId => $group) {
			$groupData = $this->groupDataProvider->getGroupData($group, $user, $portfolio, $dateTime);

			$groupsWithGroupData[] = new GroupWithGroupDataDto(
				id: $groupId,
				userId: $user->id,
				name: $group->name,
				color: $group->color,
				assetIds: array_map(fn (AssetWithPropertiesDto $asset): int => $asset->id, $groupAssets[$groupId]),
				assets: $groupAssets[$groupId],
				percentage: CalculatorUtils::toPercentage($groupData->value, $portfolioData->value),
				groupData: GroupDataDto::fromCalculatedDataDto($groupData),
			);
		}

		return $groupsWithGroupData;
	}
}
