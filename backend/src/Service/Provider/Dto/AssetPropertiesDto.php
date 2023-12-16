<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

use Decimal\Decimal;

readonly class AssetPropertiesDto
{
	public function __construct(
		public Decimal $price,
		public Decimal $units,
		public Decimal $value,
		public Decimal $transactionValue,
		public Decimal $gain,
		public Decimal $gainDefaultCurrency,
		public float $gainPercentage,
		public Decimal $dividendGain,
		public Decimal $dividendGainDefaultCurrency,
		public float $dividendGainPercentage,
		public Decimal $fxImpact,
		public float $fxImpactPercentage,
		public Decimal $return,
		public float $returnPercentage,
	) {
	}
}
