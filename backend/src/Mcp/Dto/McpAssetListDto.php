<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpAssetListDto
{
	/** @param list<McpAssetDto> $assets */
	public function __construct(public array $assets)
	{
	}
}
