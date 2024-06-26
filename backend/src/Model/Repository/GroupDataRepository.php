<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\GroupData;

/** @extends ARepository<GroupData> */
final class GroupDataRepository extends ARepository
{
	public function findGroupData(int $groupId, DateTimeImmutable $date): ?GroupData
	{
		return $this->findOne([
			'group_id' => $groupId,
			'date' => $date,
		]);
	}

	public function deleteGroupData(int $groupId, ?DateTimeImmutable $date = null): void
	{
		$deleteGroupData = $this->orm->getSource(GroupData::class)
			->getDatabase()
			->delete('group_datas')
			->where('group_id', $groupId);

		if ($date !== null) {
			$deleteGroupData->where('date', '>=', $date);
		}

		$deleteGroupData->run();
	}

	public function deleteUserGroupData(?int $userId = null, ?int $portfolioId = null, ?DateTimeImmutable $date = null): void
	{
		$deleteUserGroupData = $this->orm->getSource(GroupData::class)
			->getDatabase()
			->delete('group_datas');

		if ($userId !== null) {
			$deleteUserGroupData->where('user_id', $userId);
		}

		if ($portfolioId !== null) {
			$deleteUserGroupData->where('portfolio_id', $portfolioId);
		}

		if ($date !== null) {
			$deleteUserGroupData->where('date', '>=', $date);
		}

		$deleteUserGroupData->run();
	}
}
