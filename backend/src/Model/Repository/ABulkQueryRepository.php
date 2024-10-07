<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeInterface;
use FinGather\Model\Entity\BulkQueryEntityInterface;

/**
 * @template TEntity of BulkQueryEntityInterface
 * @extends ARepository<TEntity>
 * @implements BulkQueryRepositoryInterface<TEntity>
 */
abstract class ABulkQueryRepository extends ARepository implements BulkQueryRepositoryInterface
{
	/** @var list<TEntity> */
	private array $bulkInsertEntities = [];

	/** @var list<array{0: string, 1: string|int|DateTimeInterface|null}> */
	private array $bulkDeleteWhere = [];

	public function runBulkInsert(): void
	{
		if (count($this->bulkInsertEntities) === 0) {
			return;
		}

		$source = $this->orm->getSource($this->bulkInsertEntities[0]::class);

		$insert = $source->getDatabase()->insert($source->getTable());
		$insert->columns($this->bulkInsertEntities[0]->getBulkInsertColumns());

		foreach ($this->bulkInsertEntities as $entity) {
			$insert->values($entity->getBulkInsertValues());
		}

		$insert->run();
	}

	public function runBulkDelete(): void
	{
		if (count($this->bulkDeleteWhere) === 0) {
			return;
		}

		$source = $this->orm->getSource($this->bulkInsertEntities[0]::class);

		$delete = $source->getDatabase()->delete($source->getTable());

		foreach ($this->bulkDeleteWhere as $where) {
			$delete->where($where[0], $where[1]);
		}

		$delete->run();
	}

	/** @param TEntity $entity */
	public function addToBulkInsert(BulkQueryEntityInterface $entity): void
	{
		$this->bulkInsertEntities[] = $entity;
	}

	public function addToBulkDelete(string $whereColumn, string|int|DateTimeInterface|null $whereValue): void
	{
		$this->bulkDeleteWhere[] = [$whereColumn, $whereValue];
	}
}
