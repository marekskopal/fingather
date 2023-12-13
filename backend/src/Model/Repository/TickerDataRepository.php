<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\TickerData;
use Safe\DateTime;

/** @extends Repository<TickerData> */
class TickerDataRepository extends Repository
{
	public function findLastTickerData(int $tickerId, DateTime $beforeDate): ?TickerData
	{
		return $this->select()->where([
			'ticker_id' => $tickerId,
			'date <= ?' => $beforeDate
		])->orderBy('date DESC')->fetchOne();
	}
}
