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

final readonly class StrategyComparisonProvider implements StrategyComparisonProviderInterface
{
	public function __construct(
		private PortfolioDataProviderInterface $portfolioDataProvider,
		private AssetProviderInterface $assetProvider,
		private AssetDataProviderInterface $assetDataProvider,
		private GroupDataProviderInterface $groupDataProvider,
		private GroupProviderInterface $groupProvider,
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
					differencePercentage: CalculatorUtils::roundPercentage($actualPercentage - $targetPercentage),
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
					differencePercentage: CalculatorUtils::roundPercentage($actualPercentage - $targetPercentage),
				);
			}
		}

		$othersTargetPercentage = CalculatorUtils::roundPercentage(100.0 - $totalTargetPercentage);
		$othersActualPercentage = CalculatorUtils::roundPercentage(100.0 - $totalActualPercentage);

		if ($othersTargetPercentage > 0.0 || $othersActualPercentage > 0.0) {
			$comparisonItems[] = new StrategyComparisonItemDto(
				name: 'Others',
				color: null,
				assetId: null,
				groupId: null,
				isOthers: true,
				targetPercentage: $othersTargetPercentage,
				actualPercentage: $othersActualPercentage,
				differencePercentage: CalculatorUtils::roundPercentage($othersActualPercentage - $othersTargetPercentage),
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
