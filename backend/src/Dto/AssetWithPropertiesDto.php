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
		public Decimal $price,
		public Decimal $units,
		public Decimal $value,
		public Decimal $transactionValue,
		public Decimal $transactionValueDefaultCurrency,
		public Decimal $gain,
		public Decimal $gainDefaultCurrency,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public Decimal $dividendGain,
		public Decimal $dividendGainDefaultCurrency,
		public float $dividendGainPercentage,
		public float $dividendGainPercentagePerAnnum,
		public Decimal $fxImpact,
		public float $fxImpactPercentage,
		public float $fxImpactPercentagePerAnnum,
		public Decimal $return,
		public float $returnPercentage,
		public float $returnPercentagePerAnnum,
	) {
	}

	public static function fromEntity(Asset $asset, AssetData $assetData): self
	{
		return new self(
			id: $asset->getId(),
			tickerId: $asset->getTicker()->getId(),
			ticker: TickerDto::fromEntity($asset->getTicker()),
			groupId: $asset->getGroup()->getId(),
			price: $assetData->getPrice(),
			units: $assetData->getUnits(),
			value: $assetData->getValue(),
			transactionValue: $assetData->getTransactionValue(),
			transactionValueDefaultCurrency: $assetData->getTransactionValueDefaultCurrency(),
			gain: $assetData->getGain(),
			gainDefaultCurrency: $assetData->getGainDefaultCurrency(),
			gainPercentage: $assetData->getGainPercentage(),
			gainPercentagePerAnnum: $assetData->getGainPercentagePerAnnum(),
			dividendGain: $assetData->getDividendGain(),
			dividendGainDefaultCurrency: $assetData->getDividendGainDefaultCurrency(),
			dividendGainPercentage: $assetData->getDividendGainPercentage(),
			dividendGainPercentagePerAnnum: $assetData->getDividendGainPercentagePerAnnum(),
			fxImpact: $assetData->getFxImpact(),
			fxImpactPercentage: $assetData->getFxImpactPercentage(),
			fxImpactPercentagePerAnnum: $assetData->getFxImpactPercentagePerAnnum(),
			return: $assetData->getReturn(),
			returnPercentage: $assetData->getReturnPercentage(),
			returnPercentagePerAnnum: $assetData->getReturnPercentagePerAnnum(),
		);
	}
}
