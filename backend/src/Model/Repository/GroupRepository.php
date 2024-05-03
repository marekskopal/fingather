<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Group;

/** @extends ARepository<Group> */
final class GroupRepository extends ARepository
{
	/** @return iterable<Group> */
	public function findGroups(int $userId, int $portfolioId): iterable
	{
		return $this->findAll([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'is_others' => false,
		]);
	}

	public function findGroup(int $userId, int $groupId): ?Group
	{
		return $this->findOne([
			'user_id' => $userId,
			'id' => $groupId,
		]);
	}

	public function findOthersGroup(int $userId, int $portfolioId): Group
	{
		$othersGroup = $this->findOne([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'is_others' => true,
		]);
		assert($othersGroup instanceof Group);
		return $othersGroup;
	}
}
