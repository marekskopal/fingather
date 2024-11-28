<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Repository\CountryRepository;

#[Entity(repository: CountryRepository::class)]
class Country extends AEntity
{
	public function __construct(
		#[Column(type: 'string(2)')]
		public readonly string $isoCode,
		#[Column(type: 'string(3)')]
		public readonly string $isoCode3,
		#[Column(type: 'string(50)')]
		public readonly string $name,
		#[Column(type: 'boolean')]
		public readonly bool $isOthers,
	) {
	}
}
