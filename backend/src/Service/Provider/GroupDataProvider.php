<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\Database\Exception\StatementException\ConstrainException;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\GroupData;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\GroupDataRepository;
use FinGather\Service\DataCalculator\DataCalculator;
use Safe\DateTimeImmutable;

class GroupDataProvider
{
	public function __construct(
		private readonly GroupDataRepository $groupDataRepository,
		private readonly DataCalculator $dataCalculator,
		private readonly AssetProvider $assetProvider,
	) {
	}

	public function getGroupData(Group $group, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): GroupData
	{
		$dateTime = $dateTime->setTime(0, 0);

		$groupData = $this->groupDataRepository->findGroupData($group->getId(), $dateTime);
		if ($groupData !== null) {
			return $groupData;
		}

		$assetDtos = [];

		$assets = $this->assetProvider->getOpenAssetsByGroup($group, $user, $portfolio, $dateTime);
		foreach ($assets as $asset) {
			$assetProperties = $this->assetProvider->getAssetProperties($user, $portfolio, $asset, $dateTime);
			if ($assetProperties === null) {
				continue;
			}

			$assetDtos[] = AssetWithPropertiesDto::fromEntity($asset, $assetProperties);
		}

		$calculatedData = $this->dataCalculator->calculate($assetDtos);

		$groupData = new GroupData(
			group: $group,
			user: $user,
			portfolio: $portfolio,
			date: $dateTime,
			value: (string) $calculatedData->value,
			transactionValue: (string) $calculatedData->transactionValue,
			gain: (string) $calculatedData->gain,
			gainPercentage: $calculatedData->gainPercentage,
			dividendGain: (string) $calculatedData->dividendGain,
			dividendGainPercentage: $calculatedData->dividendGainPercentage,
			fxImpact: (string) $calculatedData->fxImpact,
			fxImpactPercentage: $calculatedData->fxImpactPercentage,
			return: (string) $calculatedData->return,
			returnPercentage: $calculatedData->returnPercentage,
		);

		try {
			$this->groupDataRepository->persist($groupData);
		} catch (ConstrainException) {
			//ignore duplicate data
		}

		return $groupData;
	}

	public function deleteUserGroupData(User $user, Portfolio $portfolio, DateTimeImmutable $date): void
	{
		$this->groupDataRepository->deleteUserGroupData($user->getId(), $portfolio->getId(), $date);
	}
}
