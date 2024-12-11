<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\SectorRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;

#[Entity(repositoryClass: SectorRepository::class)]
class Sector extends AEntity
{
	public function __construct(#[Column(type: 'string')] private string $name, #[Column(type: 'boolean')] private bool $isOthers,)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}
}
