<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\DcaPlan;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<DcaPlan> */
final class DcaPlanRepository extends AbstractRepository
{
	/** @return Iterator<DcaPlan> */
	public function findDcaPlans(int $userId, int $portfolioId): Iterator
	{
		return $this->select()
			->where(['user_id' => $userId, 'portfolio_id' => $portfolioId])
			->fetchAll();
	}

	public function findDcaPlan(int $dcaPlanId, int $userId): ?DcaPlan
	{
		return $this->select()
			->where(['id' => $dcaPlanId, 'user_id' => $userId])
			->fetchOne();
	}
}
