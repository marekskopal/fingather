<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Asset;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;

final readonly class McpAssetDetailDto
{
	public function __construct(
		public int $assetId,
		public string $ticker,
		public string $name,
		public string $type,
		public ?string $sector,
		public ?string $country,
		public string $units,
		public string $price,
		public string $value,
		public string $averagePrice,
		public string $transactionValue,
		public string $gain,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public string $realizedGain,
		public string $dividendYield,
		public float $dividendYieldPercentage,
		public float $dividendYieldPercentagePerAnnum,
		public string $fxImpact,
		public float $fxImpactPercentage,
		public string $return,
		public float $returnPercentage,
		public float $returnPercentagePerAnnum,
		public string $tax,
		public string $fee,
		public string $firstTransactionDate,
	) {
	}

	public static function fromAssetData(Asset $asset, AssetDataDto $data): self
	{
		return new self(
			assetId: $asset->id,
			ticker: $asset->ticker->ticker,
			name: $asset->ticker->name,
			type: $asset->ticker->type->value,
			sector: $asset->ticker->sector->name,
			country: $asset->ticker->country->name,
			units: (string) $data->units,
			price: (string) $data->price,
			value: (string) $data->value,
			averagePrice: (string) $data->averagePriceDefaultCurrency,
			transactionValue: (string) $data->transactionValueDefaultCurrency,
			gain: (string) $data->gainDefaultCurrency,
			gainPercentage: $data->gainPercentage,
			gainPercentagePerAnnum: $data->gainPercentagePerAnnum,
			realizedGain: (string) $data->realizedGainDefaultCurrency,
			dividendYield: (string) $data->dividendYieldDefaultCurrency,
			dividendYieldPercentage: $data->dividendYieldPercentage,
			dividendYieldPercentagePerAnnum: $data->dividendYieldPercentagePerAnnum,
			fxImpact: (string) $data->fxImpact,
			fxImpactPercentage: $data->fxImpactPercentage,
			return: (string) $data->return,
			returnPercentage: $data->returnPercentage,
			returnPercentagePerAnnum: $data->returnPercentagePerAnnum,
			tax: (string) $data->taxDefaultCurrency,
			fee: (string) $data->feeDefaultCurrency,
			firstTransactionDate: $data->firstTransactionActionCreated->format('Y-m-d'),
		);
	}
}
