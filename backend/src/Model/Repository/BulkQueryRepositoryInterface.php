<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeInterface;
use FinGather\Model\Entity\BulkQueryEntityInterface;

/**
 * @template TEntity of BulkQueryEntityInterface
 * @extends RepositoryInterface<TEntity>
 */
interface BulkQueryRepositoryInterface extends RepositoryInterface
{
	public function runBulkInsert(): void;

	public function runBulkDelete(): void;

	public function addToBulkInsert(BulkQueryEntityInterface $entity): void;

	public function addToBulkDelete(string $whereColumn, string|int|DateTimeInterface|null $whereValue): void;
}
