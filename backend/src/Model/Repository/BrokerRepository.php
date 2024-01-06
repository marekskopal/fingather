<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Broker;

/** @extends ARepository<Broker> */
class BrokerRepository extends ARepository
{
	/** @return iterable<Broker> */
	public function findBrokers(int $userId): iterable
	{
		return $this->findAll([
			'user_id' => $userId,
		]);
	}

	public function findBroker(int $brokerId, int $userId): ?Broker
	{
		return $this->findOne([
			'id' => $brokerId,
			'user_id' => $userId,
		]);
	}
}
