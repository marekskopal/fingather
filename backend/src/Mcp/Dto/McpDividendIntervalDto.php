<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Service\DataCalculator\Dto\DividendDataIntervalDto;

final readonly class McpDividendIntervalDto
{
	/** @param list<McpDividendAssetDto> $assets */
	public function __construct(
		public string $interval,
		public array $assets,
	) {
	}

	public static function fromDividendDataInterval(DividendDataIntervalDto $dto): self
	{
		$assets = [];
		foreach ($dto->dividendDataAssets as $asset) {
			$assets[] = McpDividendAssetDto::fromDividendDataAsset($asset);
		}

		return new self(
			interval: $dto->interval,
			assets: $assets,
		);
	}
}
