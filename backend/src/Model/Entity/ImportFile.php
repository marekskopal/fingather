<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\ImportFileRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: ImportFileRepository::class)]
class ImportFile extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Import::class)]
		private Import $import,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $created,
		#[Column(type: 'string')]
		private string $fileName,
		#[Column(type: 'longBinary')]
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
