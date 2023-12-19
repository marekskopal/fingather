<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\GroupData;

/** @extends ARepository<GroupData> */
class GroupDataRepository extends ARepository
{
	public function deleteGroupData(int $userId, DateTimeImmutable $date): void
	{
		$this->orm->getSource(GroupData::class)
			->getDatabase()
			->delete('group_datas')
			->where('user_id', $userId)
			->where('date', '>=', $date)
			->run();
	}
}
