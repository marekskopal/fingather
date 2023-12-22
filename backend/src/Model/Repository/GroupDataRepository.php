<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\GroupData;

/** @extends ARepository<GroupData> */
class GroupDataRepository extends ARepository
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

	public function deleteUserGroupData(int $userId, DateTimeImmutable $date): void
	{
		$this->orm->getSource(GroupData::class)
			->getDatabase()
			->delete('group_datas')
			->where('user_id', $userId)
			->where('date', '>=', $date)
			->run();
	}
}
