<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Repository\IndustryRepository;

#[Entity(repository: IndustryRepository::class)]
class Industry extends AEntity
{
	public function __construct(#[Column(type: 'string')] private string $name, #[Column(type: 'boolean')] private bool $isOthers,)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}
}
