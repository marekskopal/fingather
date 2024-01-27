<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Broker;

/** @extends ARepository<Broker> */
class BrokerRepository extends ARepository
{
	/** @return iterable<Broker> */
	public function findBrokers(int $userId, int $portfolioId): iterable
	{
		return $this->findAll([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
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
