<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\User;

readonly class CalculatedDataDto
{
	public function __construct(
		public User $user,
		public DateTimeImmutable $date,
		public Decimal $value,
		public Decimal $transactionValue,
		public Decimal $gain,
		public float $gainPercentage,
		public Decimal $dividendGain,
		public float $dividendGainPercentage,
		public Decimal $fxImpact,
		public float $fxImpactPercentage,
		public Decimal $return,
		public float $returnPercentage,
		public float $performance,
	) {
	}
}
