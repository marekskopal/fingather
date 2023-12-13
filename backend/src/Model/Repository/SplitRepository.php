<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\Split;

/** @extends ARepository<Split> */
class SplitRepository extends ARepository
{
	public function findSplits(int $tickerId)
	{
		return $this->findAll([
			'ticker_id' => $tickerId
		]);
	}
}
