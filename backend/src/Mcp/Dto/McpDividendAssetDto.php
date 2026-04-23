<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Service\DataCalculator\Dto\DividendDataAssetDto;

final readonly class McpDividendAssetDto
{
	public function __construct(
		public int $assetId,
		public string $ticker,
		public string $name,
		public string $dividendYield,
	) {
	}

	public static function fromDividendDataAsset(DividendDataAssetDto $dto): self
	{
		return new self(
			assetId: $dto->id,
			ticker: $dto->tickerTicker,
			name: $dto->tickerName,
			dividendYield: (string) $dto->dividendYield,
		);
	}
}
