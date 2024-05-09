<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\Split;

/** @extends ARepository<Split> */
final class SplitRepository extends ARepository
{
	/** @return list<Split> */
	public function findSplits(int $tickerId): iterable
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
