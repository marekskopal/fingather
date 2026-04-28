<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpDcaProjectionsDto
{
	/** @param list<McpDcaPlanDto> $plans */
	public function __construct(public int $portfolioId, public string $currency, public array $plans,)
	{
	}
}
