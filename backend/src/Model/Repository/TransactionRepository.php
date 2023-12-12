<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Transaction;
use Safe\DateTime;

/** @extends ARepository<Transaction> */
class TransactionRepository extends ARepository
{
	/** @return array<Transaction> */
	public function findOpenTransactions(int $userId, DateTime $dateTime): array
	{
		return $this->orm->getSource(Transaction::class)
			->getDatabase()
			->select()
			->where([
				'user_id' => $userId,
				'created <= ?' => $dateTime->getTimestamp(),
			])
			->groupBy('asset_id')
			->having('SUM(units)>0')
			->fetchAll();
	}

	public function findTransactionByIdentifier(int $brokerId, string $identifier): ?Transaction
	{
		return $this->findOne([
			'broker_id' => $brokerId,
			'import_identifier' => $identifier,
		]);
	}
}
