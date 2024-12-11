<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ImportMapping;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<ImportMapping> */
final class ImportMappingRepository extends AbstractRepository
{
	/** @return Iterator<ImportMapping> */
	public function findImportMappings(int $userId, int $portfolioId, int $brokerId): Iterator
	{
		return $this->findAll([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'broker_id' => $brokerId,
		]);
	}
}
