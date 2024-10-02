<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Repository\ImportFileRepository;

#[Entity(repository: ImportFileRepository::class)]
class ImportFile extends AEntity
{
	public function __construct(
		#[RefersTo(target: Import::class)]
		private Import $import,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $created,
		#[Column(type: 'string')]
		private string $fileName,
		#[Column(type: 'longText')]
		private string $contents,
	) {
	}

	public function getImport(): Import
	{
		return $this->import;
	}

	public function getFileName(): string
	{
		return $this->fileName;
	}

	public function getContents(): string
	{
		return $this->contents;
	}
}
