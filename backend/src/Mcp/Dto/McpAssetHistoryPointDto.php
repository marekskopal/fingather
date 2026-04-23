<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Service\DataCalculator\Dto\AssetDataDto;

final readonly class McpAssetHistoryPointDto
{
	public function __construct(
		public string $date,
		public string $price,
		public string $units,
		public string $value,
		public string $transactionValue,
		public string $gain,
		public float $gainPercentage,
		public string $return,
		public float $returnPercentage,
	) {
	}

	public static function fromAssetData(AssetDataDto $data): self
	{
		return new self(
			date: $data->date->format('Y-m-d'),
			price: (string) $data->price,
			units: (string) $data->units,
			value: (string) $data->value,
			transactionValue: (string) $data->transactionValueDefaultCurrency,
			gain: (string) $data->gainDefaultCurrency,
			gainPercentage: $data->gainPercentage,
			return: (string) $data->return,
			returnPercentage: $data->returnPercentage,
		);
	}
}
