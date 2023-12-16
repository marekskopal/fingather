<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Brick\Math\BigDecimal;
use FinGather\Model\Entity\Asset;
use FinGather\Service\Provider\Dto\AssetPropertiesDto;

final readonly class AssetDto
{
	public function __construct(
		public int $id,
		public int $tickerId,
		public TickerDto $ticker,
		public ?int $groupId,
		public BigDecimal $price,
		public BigDecimal $units,
		public BigDecimal $value,
		public BigDecimal $transactionValue,
		public BigDecimal $gain,
		public BigDecimal $gainDefaultCurrency,
		public float $gainPercentage,
		public BigDecimal $dividendGain,
		public BigDecimal $dividendGainDefaultCurrency,
		public float $dividendGainPercentage,
		public BigDecimal $fxImpact,
		public float $fxImpactPercentage,
		public BigDecimal $return,
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
