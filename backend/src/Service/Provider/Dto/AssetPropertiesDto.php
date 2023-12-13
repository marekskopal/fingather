<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

readonly class AssetPropertiesDto
{
	public function __construct(
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
}
