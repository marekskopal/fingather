<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Repository\TickerRepository;

#[Entity(repository: TickerRepository::class)]
class TickerSector extends AEntity
{
	public function __construct(#[Column(type: 'string')] private string $name,)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}
}
