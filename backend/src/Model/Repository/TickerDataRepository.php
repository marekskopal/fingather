<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\TickerData;
use Safe\DateTime;

/** @extends ARepository<TickerData> */
class TickerDataRepository extends ARepository
{
	public function findLastTickerData(int $tickerId, ?DateTime $beforeDate = null): ?TickerData
	{
		$where = [
			'ticker_id' => $tickerId,
		];

		if ($beforeDate !== null) {
			$where['date <= ?'] = $beforeDate;
		}

		return $this->select()
			->where($where)
			->orderBy('date DESC')
			->fetchOne();
	}
}
