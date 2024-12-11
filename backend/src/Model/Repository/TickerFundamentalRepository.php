<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\TickerFundamental;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<TickerFundamental> */
final class TickerFundamentalRepository extends AbstractRepository
{
	public function findTickerFundamental(int $tickerId): ?TickerFundamental
	{
		return $this->findOne([
			'ticker_id' => $tickerId,
		]);
	}
}
