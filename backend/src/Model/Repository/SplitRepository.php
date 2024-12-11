<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\Split;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Split> */
final class SplitRepository extends AbstractRepository
{
	/** @return Iterator<Split> */
	public function findSplits(int $tickerId): Iterator
	{
		return $this->select()
			->where([
				'ticker_id' => $tickerId,
			])
			->orderBy('date')
			->fetchAll();
	}

	public function findSplit(int $tickerId, ?DateTimeImmutable $date = null): ?Split
	{
		$where = [
			'ticker_id' => $tickerId,
		];
		if ($date !== null) {
			$where['date'] = $date;
		}

		return $this->findOne($where);
	}
}
