<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select;
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
		?int $assetId = null,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
		?int $limit = null,
		?int $offset = null,
	): array {
		return $this->getTransactionsSelect($userId, $assetId, $actionCreatedBefore, $actionTypes, $limit, $offset)->fetchAll();
	}

	/** @param list<TransactionActionTypeEnum> $actionTypes */
	public function countTransactions(
		int $userId,
		?int $assetId = null,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
	): int {
		return $this->getTransactionsSelect($userId, $assetId, $actionCreatedBefore, $actionTypes)->count();
	}

	/**
	 * @param list<TransactionActionTypeEnum> $actionTypes
	 * @return Select<Transaction>
	 */
	private function getTransactionsSelect(
		int $userId,
		?int $assetId = null,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
		?int $limit = null,
		?int $offset = null,
	): Select {
		$transactions = $this->select()
			->where('user_id', $userId);

		if ($assetId !== null) {
			$transactions->where('asset_id', $assetId);
		}

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

		$transactions->orderBy('action_created', 'DESC');

		return $transactions;
	}

	public function findTransaction(int $transactionId, int $userId): ?Transaction
	{
		return $this->findOne([
			'id' => $transactionId,
			'user_id' => $userId,
		]);
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
