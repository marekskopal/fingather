<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Industry;

final readonly class TickerIndustryDto
{
	public function __construct(public int $id, public string $name,)
	{
	}

	public static function fromEntity(Industry $tickerIndustry): self
	{
		return new self(
			id: $tickerIndustry->getId(),
			name: $tickerIndustry->getName(),
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
