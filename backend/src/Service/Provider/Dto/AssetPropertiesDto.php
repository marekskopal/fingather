<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

use DateTimeImmutable;
use Decimal\Decimal;

readonly class AssetPropertiesDto
{
	public function __construct(
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
		public DateTimeImmutable $firstTransactionActionCreated,
	) {
	}

	public function isOpen(): bool
	{
		return $this->units->isPositive() && !$this->units->isZero();
	}

	public function isClosed(): bool
	{
		return !$this->isOpen();
	}
}
