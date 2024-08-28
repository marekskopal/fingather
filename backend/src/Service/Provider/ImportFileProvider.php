<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\ImportFile;
use FinGather\Model\Repository\ImportFileRepository;
use Safe\DateTimeImmutable;

class ImportFileProvider
{
	public function __construct(private readonly ImportFileRepository $importFileRepository)
	{
	}

	/** @return iterable<ImportFile> */
	public function getImportFiles(Import $import): iterable
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
}
