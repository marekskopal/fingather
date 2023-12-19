<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Transaction;
use Safe\DateTimeImmutable;

/** @extends ARepository<Transaction> */
class TransactionRepository extends ARepository
{
	/** @return array<int,Transaction> */
	public function findAssetTransactions(int $assetId, DateTimeImmutable $dateTime): array
	{
		return $this->select()
			->where('asset_id', $assetId)
			->where('created', '<=', $dateTime)
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
