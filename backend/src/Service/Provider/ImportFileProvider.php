<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\ImportFile;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ImportFileRepository;

class ImportFileProvider
{
	public function __construct(private readonly ImportFileRepository $importFileRepository)
	{
	}

	public function getImportFile(int $importFileId, User $user): ?ImportFile
	{
		$importFile = $this->importFileRepository->findImportFile($importFileId);
		if ($importFile === null) {
			return null;
		}

		if ($importFile->getImport()->getUser()->getId() !== $user->getId()) {
			return null;
		}

		return $importFile;
	}

	/** @return list<ImportFile> */
	public function getImportFiles(Import $import): array
	{
		return $this->importFileRepository->findImportFiles($import->getId());
	}

	public function createImportFile(Import $import, string $fileName, string $contents): ImportFile
	{
		$importFile = new ImportFile(
			import: $import,
			created: new DateTimeImmutable(),
			fileName: $fileName,
			contents: $contents,
		);
		$this->importFileRepository->persist($importFile);

		return $importFile;
	}

	public function deleteImportFile(ImportFile $importFile): void
	{
		$this->importFileRepository->delete($importFile);
	}
}
