<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\PriceAlert;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<PriceAlert> */
final class PriceAlertRepository extends AbstractRepository
{
	/** @return list<PriceAlert> */
	public function findPriceAlerts(int $userId): array
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

	/** @return list<PriceAlert> */
	public function findActivePriceAlerts(): array
	{
		return $this->select()
			->where(['is_active' => true])
			->fetchAll();
	}
}
