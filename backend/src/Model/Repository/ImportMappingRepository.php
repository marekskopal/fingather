<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ImportMapping;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<ImportMapping> */
final class ImportMappingRepository extends AbstractRepository
{
	/** @return list<ImportMapping> */
	public function findImportMappings(int $userId, int $portfolioId, int $brokerId): array
	{
		return $this->findAll([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'broker_id' => $brokerId,
		]);
	}

	/** @return list<ImportMapping> */
	public function findByPortfolio(int $userId, int $portfolioId): array
	{
		return $this->findAll([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
		]);
	}

	public function findById(int $id): ?ImportMapping
	{
		return $this->findOne(['id' => $id]);
	}
}
