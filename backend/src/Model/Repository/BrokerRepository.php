<?php

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\Broker;

class BrokerRepository extends Repository
{
	/**
	 * @return array<Broker>
	 */
	public function findBrokers(int $userId): array
	{
		return $this->findAll([
			'user.id' => $userId,
		]);
	}
}