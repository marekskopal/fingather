<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\PriceAlert;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<PriceAlert> */
final class PriceAlertRepository extends AbstractRepository
{
	/** @return Iterator<PriceAlert> */
	public function findPriceAlerts(int $userId): Iterator
	{
		return $this->select()
			->where(['user_id' => $userId])
			->fetchAll();
	}

	public function findPriceAlert(int $priceAlertId, int $userId): ?PriceAlert
	{
		return $this->select()
			->where(['id' => $priceAlertId, 'user_id' => $userId])
			->fetchOne();
	}

	/** @return Iterator<PriceAlert> */
	public function findActivePriceAlerts(): Iterator
	{
		return $this->select()
			->where(['is_active' => true])
			->fetchAll();
	}
}
