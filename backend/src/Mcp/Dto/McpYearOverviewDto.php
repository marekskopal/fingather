<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpYearOverviewDto
{
	/** @param list<McpYearDataDto> $years */
	public function __construct(
		public int $portfolioId,
		public string $currency,
		public array $years,
	) {
	}
}
