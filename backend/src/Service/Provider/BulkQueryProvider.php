<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\BulkQueryEntityInterface;
use FinGather\Model\Repository\BulkQueryRepositoryInterface;

final class BulkQueryProvider
{
	/** @var list<BulkQueryRepositoryInterface<covariant BulkQueryEntityInterface>> */
	private array $bulkQueryRepositories = [];

	/**
	 * @param BulkQueryRepositoryInterface<covariant BulkQueryEntityInterface> $repository
	 * @api
	 */
	public function addRepository(BulkQueryRepositoryInterface $repository): void
	{
		$this->bulkQueryRepositories[] = $repository;
	}

	public function runAll(): void
	{
		foreach ($this->bulkQueryRepositories as $repository) {
			$repository->getQueryProvider()->transaction(function () use ($repository): void {
				$repository->runBulkDelete();
				$repository->runBulkInsert();
			});
		}
	}
}
