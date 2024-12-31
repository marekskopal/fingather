<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\TickerData;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<TickerData> */
final class TickerDataRepository extends AbstractRepository
{
	/** @return Iterator<TickerData> */
	public function findTickerDatas(int $tickerId, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): Iterator
	{
		return $this->select()
			->where(['ticker_id' => $tickerId])
			->where(['date', '>=', $fromDate])
			->where(['date', '<=', $toDate])
			->orderBy('date', 'DESC')
			->fetchAll();
	}

	public function findLastTickerData(int $tickerId, ?DateTimeImmutable $beforeDate = null): ?TickerData
	{
		$select = $this->select()
			->where(['ticker_id' => $tickerId]);

		if ($beforeDate !== null) {
			$select->where(['date', '<=', $beforeDate]);
		}

		$select->orderBy('date', 'DESC');

		return $select->fetchOne();
	}
}
