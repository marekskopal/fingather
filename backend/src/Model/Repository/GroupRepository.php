<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Group;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Group> */
final class GroupRepository extends AbstractRepository
{
	/** @return Iterator<Group> */
	public function findGroups(int $userId, int $portfolioId): Iterator
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
