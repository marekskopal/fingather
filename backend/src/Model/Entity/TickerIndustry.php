<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Repository\TickerIndustryRepository;

#[Entity(repository: TickerIndustryRepository::class)]
class TickerIndustry extends AEntity
{
	public function __construct(#[Column(type: 'string')] private string $name,)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}
}
