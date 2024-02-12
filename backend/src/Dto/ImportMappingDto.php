<?php

declare(strict_types=1);

namespace FinGather\Dto;

readonly class ImportMappingDto
{
	public function __construct(public string $importTicker, public int $tickerId)
	{
	}

	/** @param array{importTicker: string, tickerId: int} $data */
	public static function fromArray(array $data): self
	{
		return new self(importTicker: $data['importTicker'], tickerId: $data['tickerId']);
	}
}
