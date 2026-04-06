<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Asset;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;

final readonly class McpAssetDto
{
	public function __construct(
		public int $assetId,
		public string $ticker,
		public string $name,
		public string $type,
		public string $units,
		public string $price,
		public string $value,
		public string $gain,
		public float $gainPercentage,
		public string $dividendYield,
		public string $return,
		public float $returnPercentage,
	) {
	}

	public static function fromAssetData(Asset $asset, AssetDataDto $data): self
	{
		return new self(
			assetId: $asset->id,
			ticker: $asset->ticker->ticker,
			name: $asset->ticker->name,
			type: $asset->ticker->type->value,
			units: (string) $data->units,
			price: (string) $data->price,
			value: (string) $data->value,
			gain: (string) $data->gainDefaultCurrency,
			gainPercentage: $data->gainPercentage,
			dividendYield: (string) $data->dividendYieldDefaultCurrency,
			return: (string) $data->return,
			returnPercentage: $data->returnPercentage,
		);
	}
}
