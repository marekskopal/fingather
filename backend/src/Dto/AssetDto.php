<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Asset;
use FinGather\Service\Provider\Dto\AssetPropertiesDto;

final readonly class AssetDto
{
	public function __construct(
		public int $id,
		public int $tickerId,
		public TickerDto $ticker,
		public ?int $groupId,
		public float $price,
		public float $units,
		public float $value,
		public float $transactionValue,
		public float $gain,
		public float $gainDefaultCurrency,
		public float $gainPercentage,
		public float $dividendGain,
		public float $dividendGainDefaultCurrency,
		public float $dividendGainPercentage,
		public float $fxImpact,
		public float $fxImpactPercentage,
		public float $return,
		public float $returnPercentage,
	) {
	}

	public static function fromEntity(Asset $asset, AssetPropertiesDto $assetProperties): self
	{
		return new self(
			id: $asset->getId(),
			tickerId: $asset->getTicker()->getId(),
			ticker: TickerDto::fromEntity($asset->getTicker()),
			groupId: $asset->getGroup()?->getId(),
			price: $assetProperties->price,
			units: $assetProperties->units,
			value: $assetProperties->value,
			transactionValue: $assetProperties->transactionValue,
			gain: $assetProperties->gain,
			gainDefaultCurrency: $assetProperties->gainDefaultCurrency,
			gainPercentage: $assetProperties->gainPercentage,
			dividendGain: $assetProperties->dividendGain,
			dividendGainDefaultCurrency: $assetProperties->dividendGainDefaultCurrency,
			dividendGainPercentage: $assetProperties->dividendGainPercentage,
			fxImpact: $assetProperties->fxImpact,
			fxImpactPercentage: $assetProperties->fxImpactPercentage,
			return: $assetProperties->return,
			returnPercentage: $assetProperties->returnPercentage,
		);
	}
}
