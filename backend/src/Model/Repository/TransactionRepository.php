<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select;
use DateTimeImmutable;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Model\Repository\Enum\TransactionOrderByEnum;

/** @extends ARepository<Transaction> */
final class TransactionRepository extends ARepository
{
	/**
	 * @param list<TransactionActionTypeEnum> $actionTypes
	 * @param array<value-of<TransactionOrderByEnum>,OrderDirectionEnum> $orderBy
	 * @return list<Transaction>
	 */
	public function findTransactions(
		int $userId,
		?int $portfolioId = null,
		?int $assetId = null,
		?DateTimeImmutable $actionCreatedAfter = null,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
		?DateTimeImmutable $created = null,
		?string $search = null,
		?int $limit = null,
		?int $offset = null,
		array $orderBy = [
			TransactionOrderByEnum::ActionCreated->value => OrderDirectionEnum::DESC,
		],
	): iterable {
		return $this->getTransactionsSelect(
			$userId,
			$portfolioId,
			$assetId,
			$actionCreatedAfter,
			$actionCreatedBefore,
			$actionTypes,
			$created,
			$search,
			$limit,
			$offset,
			$orderBy,
		)->fetchAll();
	}

	/** @param list<TransactionActionTypeEnum> $actionTypes */
	public function countTransactions(
		int $userId,
		?int $portfolioId = null,
		?int $assetId = null,
		?DateTimeImmutable $actionCreatedAfter = null,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
		?DateTimeImmutable $created = null,
		?string $search = null,
	): int {
		return $this->getTransactionsSelect(
			$userId,
			$portfolioId,
			$assetId,
			$actionCreatedAfter,
			$actionCreatedBefore,
			$actionTypes,
			$created,
			$search,
		)->count();
	}

	/**
	 * @param list<TransactionActionTypeEnum> $actionTypes
	 * @param array<value-of<TransactionOrderByEnum>,OrderDirectionEnum> $orderBy
	 * @return Select<Transaction>
	 */
	private function getTransactionsSelect(
		int $userId,
		?int $portfolioId = null,
		?int $assetId = null,
		?DateTimeImmutable $actionCreatedAfter = null,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
		?DateTimeImmutable $created = null,
		?string $search = null,
		?int $limit = null,
		?int $offset = null,
		array $orderBy = [
			TransactionOrderByEnum::ActionCreated->value => OrderDirectionEnum::DESC,
		],
	): Select {
		$transactions = $this->select()
			->where('user_id', $userId);

		if ($portfolioId !== null) {
			$transactions->where('portfolio_id', $portfolioId);
		}

		if ($assetId !== null) {
			$transactions->where('asset_id', $assetId);
		}

		if ($actionCreatedAfter !== null) {
			$transactions->where('action_created', '>=', $actionCreatedAfter);
		}

		if ($actionCreatedBefore !== null) {
			$transactions->where('action_created', '<=', $actionCreatedBefore);
		}

		if ($actionTypes !== null) {
			$transactions->where('action_type', 'in', array_map(fn (TransactionActionTypeEnum $item) => $item->value, $actionTypes));
		}

		if ($created !== null) {
			$transactions->where('created', '>=', $created->setTime(0, 0));
			$transactions->where('created', '<=', $created->setTime(23, 59, 59));
		}

		if ($search !== null) {
			$transactions->where(
				fn (Select\QueryBuilder $select) =>
					$select->where('asset.ticker.name', 'like', '%' . $search . '%')
					->orWhere('asset.ticker.ticker', 'like', '%' . $search . '%'),
			);
		}

		if ($limit !== null) {
			$transactions->limit($limit);
		}

		if ($offset !== null) {
			$transactions->offset($offset);
		}

		foreach ($orderBy as $column => $direction) {
			$transactions->orderBy($column, $direction->value);
		}

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

	public function findFirstTransaction(int $userId, int $portfolioId, ?int $assetId = null): ?Transaction
	{
		$firstTransactionSelect = $this->select()
			->where('user_id', $userId)
			->where('portfolio_id', $portfolioId);

		if ($assetId !== null) {
			$firstTransactionSelect->where('asset_id', $assetId);
		}

		$firstTransactionSelect->orderBy('action_created');

		return $firstTransactionSelect->fetchOne();
	}
}
