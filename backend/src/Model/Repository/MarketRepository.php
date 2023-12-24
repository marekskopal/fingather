<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Market;

/** @extends Repository<Market> */
class MarketRepository extends Repository
{
	public function findMarketByMic(string $mic): ?Market
	{
		return $this->findOne([
			'mic' => $mic,
		]);
	}

	public function findMarketByType(MarketTypeEnum $type): ?Market
	{
		return $this->findOne([
			'type' => $type->value,
		]);
	}
}
