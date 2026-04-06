<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpTickerListDto
{
	/** @param list<McpTickerDto> $tickers */
	public function __construct(public array $tickers)
	{
	}
}
