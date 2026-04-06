<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpGoalListDto
{
	/** @param list<McpGoalDto> $goals */
	public function __construct(public array $goals)
	{
	}
}
