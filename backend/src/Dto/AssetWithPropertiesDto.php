<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\AssetData;

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

	public static function fromEntity(Asset $asset, AssetData $assetData, float $percentage): self
	{
		return new self(
			id: $asset->getId(),
			tickerId: $asset->getTicker()->getId(),
			ticker: TickerDto::fromEntity($asset->getTicker()),
			groupId: $asset->getGroup()->getId(),
			isClosed: $assetData->isClosed(),
			price: $assetData->getPrice(),
			units: $assetData->getUnits(),
			value: $assetData->getValue(),
			transactionValue: $assetData->getTransactionValue(),
			transactionValueDefaultCurrency: $assetData->getTransactionValueDefaultCurrency(),
			averagePrice: $assetData->getAveragePrice(),
			averagePriceDefaultCurrency: $assetData->getAveragePriceDefaultCurrency(),
			gain: $assetData->getGain(),
			gainDefaultCurrency: $assetData->getGainDefaultCurrency(),
			gainPercentage: $assetData->getGainPercentage(),
			gainPercentagePerAnnum: $assetData->getGainPercentagePerAnnum(),
			realizedGain: $assetData->getRealizedGain(),
			realizedGainDefaultCurrency: $assetData->getRealizedGainDefaultCurrency(),
			dividendYield: $assetData->getdividendYield(),
			dividendYieldDefaultCurrency: $assetData->getdividendYieldDefaultCurrency(),
			dividendYieldPercentage: $assetData->getdividendYieldPercentage(),
			dividendYieldPercentagePerAnnum: $assetData->getdividendYieldPercentagePerAnnum(),
			fxImpact: $assetData->getFxImpact(),
			fxImpactPercentage: $assetData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $assetData->getFxImpactPercentagePerAnnum(),
			return: $assetData->getReturn(),
			returnPercentage: $assetData->getReturnPercentage(),
			returnPercentagePerAnnum: $assetData->getReturnPercentagePerAnnum(),
			tax: $assetData->getTax(),
			taxDefaultCurrency: $assetData->getTaxDefaultCurrency(),
			fee: $assetData->getFee(),
			feeDefaultCurrency: $assetData->getFeeDefaultCurrency(),
			percentage: $percentage,
		);
	}
}
