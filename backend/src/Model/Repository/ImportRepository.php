<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Import;
use MarekSkopal\ORM\Repository\AbstractRepository;
use Ramsey\Uuid\UuidInterface;

/** @extends AbstractRepository<Import> */
final class ImportRepository extends AbstractRepository
{
	public function findImportByUuid(UuidInterface $uuid, int $userId): ?Import
	{
		return $this->findOne([
			'uuid' => $uuid,
			'user_id' => $userId,
		]);
	}
}
