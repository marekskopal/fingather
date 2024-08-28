<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ImportFile;

/** @extends ARepository<ImportFile> */
final class ImportFileRepository extends ARepository
{
	/** @return iterable<ImportFile> */
	public function findImportFiles(int $importId): iterable
	{
		return $this->findAll([
			'import_id' => $importId,
		]);
	}
}
