<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ImportFile;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<ImportFile> */
final class ImportFileRepository extends AbstractRepository
{
	public function findImportFile(int $importFileId): ?ImportFile
	{
		return $this->findOne([
			'id' => $importFileId,
		]);
	}

	/** @return list<ImportFile> */
	public function findImportFiles(int $importId): array
	{
		return $this->findAll([
			'import_id' => $importId,
		]);
	}
}
