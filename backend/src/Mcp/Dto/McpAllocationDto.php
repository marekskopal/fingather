<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpAllocationDto
{
	/** @param list<McpAllocationItemDto> $items */
	public function __construct(public int $portfolioId, public string $currency, public string $allocationType, public array $items,)
	{
	}
}
