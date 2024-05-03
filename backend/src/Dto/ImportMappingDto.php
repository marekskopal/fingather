<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class ImportMappingDto
{
	public function __construct(public string $importTicker, public int $tickerId, public int $brokerId)
	{
	}

	/** @param array{importTicker: string, tickerId: int, brokerId: int} $data */
	public static function fromArray(array $data): self
	{
		return new self(importTicker: $data['importTicker'], tickerId: $data['tickerId'], brokerId: $data['brokerId']);
	}
}
