<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpStrategyListDto
{
	/** @param list<McpStrategyDto> $strategies */
	public function __construct(public array $strategies)
	{
	}
}
