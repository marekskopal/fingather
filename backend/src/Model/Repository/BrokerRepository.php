<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\Broker;

/** @extends Repository<Broker> */
class BrokerRepository extends Repository
{
	/** @return iterable<Broker> */
	public function findBrokers(int $userId): iterable
	{
		return $this->findAll([
			'user.id' => $userId,
		]);
	}

	public function findBroker(int $brokerId, int $userId): ?Broker
	{
		return $this->findOne([
			'id' => $brokerId,
			'user.id' => $userId,
		]);
	}
}
