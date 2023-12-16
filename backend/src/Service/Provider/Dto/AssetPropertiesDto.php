<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

use Brick\Math\BigDecimal;

readonly class AssetPropertiesDto
{
	public function __construct(
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
}
