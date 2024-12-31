<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\IndustryRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: IndustryRepository::class)]
class Industry extends AEntity
{
	public function __construct(
		#[Column(type: Type::String)] public readonly string $name,
		#[Column(type: Type::Boolean, default: false)] public readonly bool $isOthers,
	)
	{
	}
}
