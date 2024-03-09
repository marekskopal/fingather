<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Service\Provider\Dto\AssetPropertiesDto;

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

	public static function fromEntity(Asset $asset, AssetPropertiesDto $assetProperties): self
	{
		return new self(
			id: $asset->getId(),
			tickerId: $asset->getTicker()->getId(),
			ticker: TickerDto::fromEntity($asset->getTicker()),
			groupId: $asset->getGroup()->getId(),
			price: $assetProperties->price,
			units: $assetProperties->units,
			value: $assetProperties->value,
			transactionValue: $assetProperties->transactionValue,
			transactionValueDefaultCurrency: $assetProperties->transactionValueDefaultCurrency,
			gain: $assetProperties->gain,
			gainDefaultCurrency: $assetProperties->gainDefaultCurrency,
			gainPercentage: $assetProperties->gainPercentage,
			gainPercentagePerAnnum: $assetProperties->gainPercentagePerAnnum,
			dividendGain: $assetProperties->dividendGain,
			dividendGainDefaultCurrency: $assetProperties->dividendGainDefaultCurrency,
			dividendGainPercentage: $assetProperties->dividendGainPercentage,
			dividendGainPercentagePerAnnum: $assetProperties->dividendGainPercentagePerAnnum,
			fxImpact: $assetProperties->fxImpact,
			fxImpactPercentage: $assetProperties->fxImpactPercentage,
			fxImpactPercentagePerAnnum: $assetProperties->fxImpactPercentagePerAnnum,
			return: $assetProperties->return,
			returnPercentage: $assetProperties->returnPercentage,
			returnPercentagePerAnnum: $assetProperties->returnPercentagePerAnnum,
		);
	}
}
