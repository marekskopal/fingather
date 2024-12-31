<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\CountryRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: CountryRepository::class)]
class Country extends AEntity
{
	public function __construct(
		#[Column(type: Type::String, size: 2)]
		public readonly string $isoCode,
		#[Column(type: Type::String, size: 3)]
		public readonly string $isoCode3,
		#[Column(type: Type::String, size: 50)]
		public readonly string $name,
		#[Column(type: Type::Boolean, default: false)]
		public readonly bool $isOthers,
	) {
	}
}
