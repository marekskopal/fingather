<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Ticker;

/** @extends ARepository<Ticker> */
class TickerRepository extends ARepository
{
	public function findTickerByTicker(string $ticker): ?Ticker
	{
		return $this->findOne([
			'ticker' => $ticker,
		]);
	}
}
