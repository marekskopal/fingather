<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\ImportFile;
use FinGather\Model\Entity\User;

interface ImportFileProviderInterface
{
	public function getImportFile(int $importFileId, User $user): ?ImportFile;

	/** @return list<ImportFile> */
	public function getImportFiles(Import $import): array;

	public function createImportFile(Import $import, string $fileName, string $contents): ImportFile;

	public function deleteImportFile(ImportFile $importFile): void;
}
