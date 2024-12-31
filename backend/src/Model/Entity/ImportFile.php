<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\ImportFileRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: ImportFileRepository::class)]
class ImportFile extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Import::class)]
		public readonly Import $import,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $created,
		#[Column(type: Type::String)]
		public readonly string $fileName,
		#[Column(type: Type::LongBlob)]
		public readonly string $contents,
	) {
	}
}
