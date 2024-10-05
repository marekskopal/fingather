<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\BulkInsertEntityInterface;

/**
 * @template TEntity of BulkInsertEntityInterface
 * @extends RepositoryInterface<TEntity>
 */
interface BulkInsertRepositoryInterface extends RepositoryInterface
{
	public function runBulkInsert(): void;

	public function addToBulkInsert(BulkInsertEntityInterface $entity): void;
}
