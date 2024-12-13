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
		public readonly Import $import,
		#[Column(type: 'timestamp')]
		public readonly DateTimeImmutable $created,
		#[Column(type: 'string')]
		public readonly string $fileName,
		#[Column(type: 'longBinary')]
		public readonly string $contents,
	) {
	}
}
