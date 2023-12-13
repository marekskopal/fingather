<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Dividend;
use Safe\DateTime;

/** @extends ARepository<Dividend> */
class DividendRepository extends ARepository
{
	/** @return iterable<Dividend> */
	public function findDividends(int $assetId, DateTime $paidDateTo): iterable
	{
		return $this->findAll([
			'asset_id' => $assetId,
			'paid_date <= ?' => $paidDateTo,
		]);
	}
}
