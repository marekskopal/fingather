<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Transaction;
use Safe\DateTimeImmutable;

/** @extends ARepository<Transaction> */
class TransactionRepository extends ARepository
{
	/**
	 * @param list<TransactionActionTypeEnum> $actionTypes
	 * @return array<int,Transaction>
	 */
	public function findTransactions(
		int $userId,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
		?int $limit = null,
		?int $offset = null
	): array
	{
		$transactions = $this->select()
			->where('user_id', $userId);

		if ($actionCreatedBefore !== null) {
			$transactions->where('action_created', '<=', $actionCreatedBefore);
		}

		if ($actionTypes !== null) {
			$transactions->where('action_type', 'in', array_map(fn (TransactionActionTypeEnum $item) => $item->value, $actionTypes));
		}

		if ($limit !== null) {
			$transactions->limit($limit);
		}

		if ($offset !== null) {
			$transactions->offset($offset);
		}

		return $transactions->fetchAll();
	}

	/**
	 * @param list<TransactionActionTypeEnum> $actionTypes
	 * @return array<int,Transaction>
	 */
	public function findAssetTransactions(
		int $userId,
		int $assetId,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null
	): array
	{
		$assetTransactions = $this->select()
			->where('user_id', $userId)
			->where('asset_id', $assetId);

		if ($actionCreatedBefore !== null) {
			$assetTransactions->where('action_created', '<=', $actionCreatedBefore);
		}

		if ($actionTypes !== null) {
			$assetTransactions->where('action_type', 'in', array_map(fn (TransactionActionTypeEnum $item) => $item->value, $actionTypes));
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
			->orderBy('action_created')
			->fetchOne();
	}
}
