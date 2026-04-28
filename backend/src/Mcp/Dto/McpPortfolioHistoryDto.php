<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpPortfolioHistoryDto
{
	/** @param list<McpPortfolioHistoryPointDto> $dataPoints */
	public function __construct(public int $portfolioId, public string $currency, public string $range, public array $dataPoints,)
	{
	}
}
