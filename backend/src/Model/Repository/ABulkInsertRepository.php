<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\BulkInsertEntityInterface;

/**
 * @template TEntity of BulkInsertEntityInterface
 * @extends ARepository<TEntity>
 * @implements BulkInsertRepositoryInterface<TEntity>
 */
abstract class ABulkInsertRepository extends ARepository implements BulkInsertRepositoryInterface
{
	/** @var list<TEntity> */
	private array $bulkInsertEntities = [];

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

	/** @param TEntity $entity */
	public function addToBulkInsert(BulkInsertEntityInterface $entity): void
	{
		$this->bulkInsertEntities[] = $entity;
	}
}
