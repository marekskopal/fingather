<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Import;

/** @extends ARepository<Import> */
class ImportRepository extends ARepository
{
	public function findImport(int $importId, int $userId): ?Import
	{
		return $this->findOne([
			'id' => $importId,
			'user_id' => $userId,
		]);
	}
}
