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
		return new self(
			id: $tickerSector->getId(),
			name: $tickerSector->getName(),
		);
	}

	/**
	 * @param array{
	 *     id: int,
	 *     name: string,
	 * } $data */
	public static function fromArray(array $data): self
	{
		return new self(id: $data['id'], name: $data['name']);
	}
}
