<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\Split;

/** @extends ARepository<Split> */
class SplitRepository extends ARepository
{
	/** @return iterable<Split> */
	public function findSplits(int $tickerId): iterable
	{
		return $this->findAll([
			'ticker_id' => $tickerId,
		]);
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
