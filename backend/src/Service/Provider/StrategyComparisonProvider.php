<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\StrategyComparisonItemDto;
use FinGather\Dto\StrategyWithComparisonDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\User;
use FinGather\Utils\CalculatorUtils;

final readonly class StrategyComparisonProvider
{
	public function __construct(
		private PortfolioDataProvider $portfolioDataProvider,
		private AssetProviderInterface $assetProvider,
		private AssetDataProviderInterface $assetDataProvider,
		private GroupDataProvider $groupDataProvider,
		private GroupProvider $groupProvider,
	) {
	}

	public function getStrategyWithComparison(
		User $user,
		Portfolio $portfolio,
		Strategy $strategy,
		DateTimeImmutable $dateTime,
	): StrategyWithComparisonDto {
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);

		// Build actual percentages for assets
		$assetPercentages = [];
		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null || $assetData->isClosed()) {
				continue;
			}

			$assetPercentages[$asset->id] = CalculatorUtils::toPercentage($assetData->value, $portfolioData->value);
		}

		// Build actual percentages for groups
		$groupPercentages = [];
		$allGroups = iterator_to_array($this->groupProvider->getGroups($user, $portfolio), false);
		$othersGroup = $this->groupProvider->getOthersGroup($user, $portfolio);
		$allGroups[] = $othersGroup;

		foreach ($allGroups as $group) {
			$groupData = $this->groupDataProvider->getGroupData($group, $user, $portfolio, $dateTime);
			$groupPercentages[$group->id] = CalculatorUtils::toPercentage($groupData->value, $portfolioData->value);
		}

		$comparisonItems = [];
		$totalTargetPercentage = 0.0;
		$totalActualPercentage = 0.0;

		foreach ($strategy->strategyItems as $item) {
			$targetPercentage = $item->percentage->toFloat();
			$totalTargetPercentage += $targetPercentage;

			if ($item->asset !== null) {
				$actualPercentage = $assetPercentages[$item->asset->id] ?? 0.0;
				$totalActualPercentage += $actualPercentage;

				$comparisonItems[] = new StrategyComparisonItemDto(
					name: $item->asset->ticker->name,
					color: null,
					assetId: $item->asset->id,
					groupId: null,
					isOthers: false,
					targetPercentage: $targetPercentage,
					actualPercentage: $actualPercentage,
					differencePercentage: round($actualPercentage - $targetPercentage, 2),
				);
			} elseif ($item->group !== null) {
				$actualPercentage = $groupPercentages[$item->group->id] ?? 0.0;
				$totalActualPercentage += $actualPercentage;

				$comparisonItems[] = new StrategyComparisonItemDto(
					name: $item->group->name,
					color: $item->group->color,
					assetId: null,
					groupId: $item->group->id,
					isOthers: false,
					targetPercentage: $targetPercentage,
					actualPercentage: $actualPercentage,
					differencePercentage: round($actualPercentage - $targetPercentage, 2),
				);
			}
		}

		$othersTargetPercentage = round(100.0 - $totalTargetPercentage, 2);
		$othersActualPercentage = round(100.0 - $totalActualPercentage, 2);

		if ($othersTargetPercentage > 0.0 || $othersActualPercentage > 0.0) {
			$comparisonItems[] = new StrategyComparisonItemDto(
				name: 'Others',
				color: null,
				assetId: null,
				groupId: null,
				isOthers: true,
				targetPercentage: $othersTargetPercentage,
				actualPercentage: $othersActualPercentage,
				differencePercentage: round($othersActualPercentage - $othersTargetPercentage, 2),
			);
		}

		return new StrategyWithComparisonDto(
			id: $strategy->id,
			name: $strategy->name,
			isDefault: $strategy->isDefault,
			comparisonItems: $comparisonItems,
		);
	}
}
