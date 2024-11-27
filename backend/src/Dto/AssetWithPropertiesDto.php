<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;

final readonly class AssetWithPropertiesDto
{
	public function __construct(
		public int $id,
		public int $tickerId,
		public TickerDto $ticker,
		public ?int $groupId,
		public bool $isClosed,
		public Decimal $price,
		public Decimal $units,
		public Decimal $value,
		public Decimal $transactionValue,
		public Decimal $transactionValueDefaultCurrency,
		public Decimal $averagePrice,
		public Decimal $averagePriceDefaultCurrency,
		public Decimal $gain,
		public Decimal $gainDefaultCurrency,
		public Decimal $realizedGain,
		public Decimal $realizedGainDefaultCurrency,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public Decimal $dividendYield,
		public Decimal $dividendYieldDefaultCurrency,
		public float $dividendYieldPercentage,
		public float $dividendYieldPercentagePerAnnum,
		public Decimal $fxImpact,
		public float $fxImpactPercentage,
		public float $fxImpactPercentagePerAnnum,
		public Decimal $return,
		public float $returnPercentage,
		public float $returnPercentagePerAnnum,
		public Decimal $tax,
		public Decimal $taxDefaultCurrency,
		public Decimal $fee,
		public Decimal $feeDefaultCurrency,
		public float $percentage,
	) {
	}

	public static function fromEntity(Asset $asset, AssetDataDto $assetData, float $percentage): self
	{
		return new self(
			id: $asset->id,
			tickerId: $asset->getTicker()->id,
			ticker: TickerDto::fromEntity($asset->getTicker()),
			groupId: $asset->getGroup()->id,
			isClosed: $assetData->isClosed(),
			price: $assetData->price,
			units: $assetData->units,
			value: $assetData->value,
			transactionValue: $assetData->transactionValue,
			transactionValueDefaultCurrency: $assetData->transactionValueDefaultCurrency,
			averagePrice: $assetData->averagePrice,
			averagePriceDefaultCurrency: $assetData->averagePriceDefaultCurrency,
			gain: $assetData->gain,
			gainDefaultCurrency: $assetData->gainDefaultCurrency,
			gainPercentage: $assetData->gainPercentage,
			gainPercentagePerAnnum: $assetData->gainPercentagePerAnnum,
			realizedGain: $assetData->realizedGain,
			realizedGainDefaultCurrency: $assetData->realizedGainDefaultCurrency,
			dividendYield: $assetData->dividendYield,
			dividendYieldDefaultCurrency: $assetData->dividendYieldDefaultCurrency,
			dividendYieldPercentage: $assetData->dividendYieldPercentage,
			dividendYieldPercentagePerAnnum: $assetData->dividendYieldPercentagePerAnnum,
			fxImpact: $assetData->fxImpact,
			fxImpactPercentage: $assetData->fxImpactPercentage,
			fxImpactPercentagePerAnnum: $assetData->fxImpactPercentagePerAnnum,
			return: $assetData->return,
			returnPercentage: $assetData->returnPercentage,
			returnPercentagePerAnnum: $assetData->returnPercentagePerAnnum,
			tax: $assetData->tax,
			taxDefaultCurrency: $assetData->taxDefaultCurrency,
			fee: $assetData->fee,
			feeDefaultCurrency: $assetData->feeDefaultCurrency,
			percentage: $percentage,
		);
	}
}
