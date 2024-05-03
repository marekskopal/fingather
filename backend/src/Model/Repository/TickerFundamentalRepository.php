<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\TickerFundamental;

/** @extends ARepository<TickerFundamental> */
final class TickerFundamentalRepository extends ARepository
{
	public function findTickerFundamental(int $tickerId): ?TickerFundamental
	{
		return $this->findOne([
			'ticker_id' => $tickerId,
		]);
	}
}
