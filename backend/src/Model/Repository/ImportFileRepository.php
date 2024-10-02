<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ImportFile;

/** @extends ARepository<ImportFile> */
final class ImportFileRepository extends ARepository
{
	public function findImportFile(int $importFileId): ?ImportFile
	{
		return $this->findOne([
			'id' => $importFileId,
		]);
	}

	/** @return iterable<ImportFile> */
	public function findImportFiles(int $importId): iterable
	{
		return $this->findAll([
			'import_id' => $importId,
		]);
	}
}
