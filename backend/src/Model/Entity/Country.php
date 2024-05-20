<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Repository\TickerRepository;

#[Entity(repository: TickerRepository::class)]
class Country extends AEntity
{
	public function __construct(
		#[Column(type: 'string')]
		private string $isoCode,
		#[Column(type: 'string')]
		private string $isoCode3,
		#[Column(type: 'string')]
		private string $name,
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
