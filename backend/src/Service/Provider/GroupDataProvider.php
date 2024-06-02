<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\Database\Exception\StatementException\ConstrainException;
use DateTimeImmutable;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\GroupData;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\GroupDataRepository;
use FinGather\Utils\DateTimeUtils;

class GroupDataProvider
{
	public function __construct(
		private readonly GroupDataRepository $groupDataRepository,
		private readonly CalculatedDataProvider $calculatedDataProvider,
	) {
	}

	public function getGroupData(Group $group, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): GroupData
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$groupData = $this->groupDataRepository->findGroupData($group->getId(), $dateTime);
		if ($groupData !== null) {
			return $groupData;
		}

		$calculatedData = $this->calculatedDataProvider->getCalculatedDate($user, $portfolio, $dateTime, $group);

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
			dividendYield: $calculatedData->dividendYield,
			dividendYieldPercentage: $calculatedData->dividendYieldPercentage,
			dividendYieldPercentagePerAnnum: $calculatedData->dividendYieldPercentagePerAnnum,
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

	public function deleteUserGroupData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->groupDataRepository->deleteUserGroupData($user?->getId(), $portfolio?->getId(), $date);
	}
}
