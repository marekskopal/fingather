<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Dividend;
use Safe\DateTimeImmutable;

/** @extends ARepository<Dividend> */
class DividendRepository extends ARepository
{
	/** @return array<int, Dividend> */
	public function findDividends(int $assetId, DateTimeImmutable $paidDateTo): array
	{
		return $this->select()
			->where('asset_id', $assetId)
			->where('paid_date', '<=', $paidDateTo)
			->fetchAll();
	}
}
