<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\Market;

/** @extends Repository<Market> */
class MarketRepository extends Repository
{
	public function findMarkerByMic(string $mic): ?Market
	{
		return $this->findOne([
			'mic' => $mic,
		]);
	}
}
