<?php

namespace FinGather\Service\Provider\Dto;

use FinGather\Dto\TickerDto;

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
		public float $percentageValueFromAll,
	) {
	}
}