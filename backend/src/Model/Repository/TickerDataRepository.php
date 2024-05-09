<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\TickerData;

/** @extends ARepository<TickerData> */
final class TickerDataRepository extends ARepository
{
	/** @return list<TickerData> */
	public function findTickerDatas(int $tickerId, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): iterable
	{
		return $this->select()
			->where('ticker_id', $tickerId)
			->where('date', '>=', $fromDate)
			->where('date', '<=', $toDate)
			->orderBy('date', 'DESC')
			->fetchAll();
	}

	public function findLastTickerData(int $tickerId, ?DateTimeImmutable $beforeDate = null): ?TickerData
	{
		$select = $this->select()
			->where('ticker_id', $tickerId);

		if ($beforeDate !== null) {
			$select->where('date', '<=', $beforeDate);
		}

		$select->orderBy('date', 'DESC');

		return $select->fetchOne();
	}
}
