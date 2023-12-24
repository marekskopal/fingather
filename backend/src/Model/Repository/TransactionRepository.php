<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Transaction;
use Safe\DateTimeImmutable;

/** @extends ARepository<Transaction> */
class TransactionRepository extends ARepository
{
	/** @return array<int,Transaction> */
	public function findTransactions(int $userId, ?DateTimeImmutable $dateTime = null): array
	{
		$assetTransactions = $this->select()
			->where('user_id', $userId);

		if ($dateTime !== null) {
			$assetTransactions->where('created', '<=', $dateTime);
		}

		return $assetTransactions->fetchAll();
	}

	/** @return array<int,Transaction> */
	public function findAssetTransactions(int $userId, int $assetId, ?DateTimeImmutable $dateTime = null): array
	{
		$assetTransactions = $this->select()
			->where('user_id', $userId)
			->where('asset_id', $assetId);

		if ($dateTime !== null) {
			$assetTransactions->where('created', '<=', $dateTime);
		}

		return $assetTransactions->fetchAll();
	}

	public function findTransactionByIdentifier(int $brokerId, string $identifier): ?Transaction
	{
		return $this->findOne([
			'broker_id' => $brokerId,
			'import_identifier' => $identifier,
		]);
	}

	public function findFirstTransaction(int $userId,): ?Transaction
	{
		return $this->select()
			->where('user_id', $userId)
			->orderBy('created')
			->fetchOne();
	}
}
