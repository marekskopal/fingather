<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Goal;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Goal> */
final class GoalRepository extends AbstractRepository
{
	/** @return Iterator<Goal> */
	public function findGoals(int $userId, int $portfolioId): Iterator
	{
		return $this->select()
			->where(['user_id' => $userId, 'portfolio_id' => $portfolioId])
			->fetchAll();
	}

	public function findGoal(int $goalId, int $userId): ?Goal
	{
		return $this->select()
			->where(['id' => $goalId, 'user_id' => $userId])
			->fetchOne();
	}

	/** @return Iterator<Goal> */
	public function findActiveGoals(): Iterator
	{
		return $this->select()
			->where(['is_active' => true])
			->fetchAll();
	}
}
