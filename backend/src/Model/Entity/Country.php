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
		private string $isoCode,
		#[Column(type: 'string(3)')]
		private string $isoCode3,
		#[Column(type: 'string(50)')]
		private string $name,
		#[Column(type: 'boolean')]
		private bool $isOthers,
	) {
	}

	public function getIsoCode(): string
	{
		return $this->isoCode;
	}

	public function getIsoCode3(): string
	{
		return $this->isoCode3;
	}

	public function getName(): string
	{
		return $this->name;
	}
}
