<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\CountryRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;

#[Entity(repositoryClass: CountryRepository::class)]
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
