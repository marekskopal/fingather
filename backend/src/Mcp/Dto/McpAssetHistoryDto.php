<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpAssetHistoryDto
{
	/** @param list<McpAssetHistoryPointDto> $dataPoints */
	public function __construct(
		public int $assetId,
		public string $ticker,
		public string $name,
		public string $currency,
		public string $range,
		public array $dataPoints,
	) {
	}
}
