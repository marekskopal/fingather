<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ImportMapping;

/** @extends ARepository<ImportMapping> */
final class ImportMappingRepository extends ARepository
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
}
