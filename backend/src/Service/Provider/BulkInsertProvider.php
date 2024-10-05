<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\BulkInsertEntityInterface;
use FinGather\Model\Repository\BulkInsertRepositoryInterface;

final class BulkInsertProvider
{
	/** @var list<BulkInsertRepositoryInterface<covariant BulkInsertEntityInterface>> */
	private array $bulkInsertRepositories = [];

	/** @param BulkInsertRepositoryInterface<covariant BulkInsertEntityInterface> $repository */
	public function addRepository(BulkInsertRepositoryInterface $repository): void
	{
		$this->bulkInsertRepositories[] = $repository;
	}

	public function bulkInsertAll(): void
	{
		foreach ($this->bulkInsertRepositories as $repository) {
			$repository->runBulkInsert();
		}
	}
}
