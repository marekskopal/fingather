<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;

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

	public function findBrokerByImportType(int $userId, int $portfolioId, BrokerImportTypeEnum $importType): ?Broker
	{
		return $this->findOne([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'import_type' => $importType->value,
		]);
	}
}
