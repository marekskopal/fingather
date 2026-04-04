<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\TickerDcfHistoryPoint;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<TickerDcfHistoryPoint> */
final class TickerDcfHistoryPointRepository extends AbstractRepository
{
	/** @return Iterator<TickerDcfHistoryPoint> */
	public function findByTicker(int $tickerId): Iterator
	{
		return $this->findAll([
			'ticker_id' => $tickerId,
		]);
	}

	public function deleteByTicker(int $tickerId): void
	{
		foreach ($this->findByTicker($tickerId) as $historyPoint) {
			$this->delete($historyPoint);
		}
	}
}
