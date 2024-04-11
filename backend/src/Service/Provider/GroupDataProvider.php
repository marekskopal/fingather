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
		private readonly AssetDataProvider $assetDataProvider,
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

		$firstTransactionActionCreated = $dateTime;

		$assets = $this->assetProvider->getAssets($user, $portfolio, $dateTime, $group);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null) {
				continue;
			}

			if ($firstTransactionActionCreated > $assetData->getFirstTransactionActionCreated()) {
				$firstTransactionActionCreated = $assetData->getFirstTransactionActionCreated();
			}

			$assetDtos[] = AssetWithPropertiesDto::fromEntity($asset, $assetData);
		}

		$calculatedData = $this->dataCalculator->calculate($assetDtos, $dateTime, $firstTransactionActionCreated);

		$groupData = new GroupData(
			group: $group,
			user: $user,
			portfolio: $portfolio,
			date: $dateTime,
			value: $calculatedData->value,
			transactionValue: $calculatedData->transactionValue,
			gain: $calculatedData->gain,
			gainPercentage: $calculatedData->gainPercentage,
			gainPercentagePerAnnum: $calculatedData->gainPercentagePerAnnum,
			realizedGain: $calculatedData->realizedGain,
			dividendGain: $calculatedData->dividendGain,
			dividendGainPercentage: $calculatedData->dividendGainPercentage,
			dividendGainPercentagePerAnnum: $calculatedData->dividendGainPercentagePerAnnum,
			fxImpact: $calculatedData->fxImpact,
			fxImpactPercentage: $calculatedData->fxImpactPercentage,
			fxImpactPercentagePerAnnum: $calculatedData->fxImpactPercentagePerAnnum,
			return: $calculatedData->return,
			returnPercentage: $calculatedData->returnPercentage,
			returnPercentagePerAnnum: $calculatedData->returnPercentagePerAnnum,
			tax: $calculatedData->tax,
			fee: $calculatedData->fee,
		);

		try {
			$this->groupDataRepository->persist($groupData);
		} catch (ConstrainException) {
			$groupData = $this->groupDataRepository->findGroupData($group->getId(), $dateTime);
			assert($groupData instanceof GroupData);
		}

		return $groupData;
	}

	public function deleteUserGroupData(User $user, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->groupDataRepository->deleteUserGroupData($user->getId(), $portfolio?->getId(), $date);
	}
}
