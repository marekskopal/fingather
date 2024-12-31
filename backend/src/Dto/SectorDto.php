<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Sector;

final readonly class SectorDto
{
	public function __construct(public int $id, public string $name,)
	{
	}

	public static function fromEntity(Sector $tickerSector): self
	{
		return new self(id: $tickerSector->id, name: $tickerSector->name);
	}
}
