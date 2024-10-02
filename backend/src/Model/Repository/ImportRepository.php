<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Import;
use Ramsey\Uuid\UuidInterface;

/** @extends ARepository<Import> */
final class ImportRepository extends ARepository
{
	public function findImportByUuid(UuidInterface $uuid, int $userId): ?Import
	{
		return $this->findOne([
			'uuid' => $uuid,
			'user_id' => $userId,
		]);
	}
}
