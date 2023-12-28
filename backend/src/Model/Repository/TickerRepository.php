<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Ticker;

/** @extends ARepository<Ticker> */
class TickerRepository extends ARepository
{
	/** @return iterable<Ticker> */
	public function findTickers(): iterable
	{
		return $this->findAll();
	}

	public function findTicker(int $tickerId): ?Ticker
	{
		return $this->findOne([
			'id' => $tickerId,
		]);
	}

	public function findTickerByTicker(string $ticker): ?Ticker
	{
		return $this->findOne([
			'ticker' => $ticker,
		]);
	}
}
