<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Dto\DcaPlanProjectionPointDto;

final readonly class McpDcaProjectionPointDto
{
	public function __construct(public string $date, public string $investedCapital, public string $projectedValue,)
	{
	}

	public static function fromProjectionPoint(DcaPlanProjectionPointDto $dto): self
	{
		return new self(date: $dto->date, investedCapital: (string) $dto->investedCapital, projectedValue: (string) $dto->projectedValue);
	}
}
