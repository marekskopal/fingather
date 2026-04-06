<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpTransactionListDto
{
	/** @param list<McpTransactionDto> $transactions */
	public function __construct(public array $transactions)
	{
	}
}
