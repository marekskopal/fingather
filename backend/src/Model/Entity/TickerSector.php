<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Repository\TickerSectorRepository;

#[Entity(repository: TickerSectorRepository::class)]
class TickerSector extends AEntity
{
	public function __construct(#[Column(type: 'string')] private string $name, #[Column(type: 'boolean')] private bool $isOthers,)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}
}
