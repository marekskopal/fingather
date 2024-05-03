<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use DateTimeImmutable;
use Decimal\Decimal;

final readonly class AssetDataDto
{
	public function __construct(
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
		public Decimal $tax,
		public Decimal $taxDefaultCurrency,
		public Decimal $fee,
		public Decimal $feeDefaultCurrency,
		public DateTimeImmutable $firstTransactionActionCreated,
	) {
	}
}
