<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpPortfolioListDto
{
	/** @param list<McpPortfolioDto> $portfolios */
	public function __construct(public array $portfolios)
	{
	}
}
