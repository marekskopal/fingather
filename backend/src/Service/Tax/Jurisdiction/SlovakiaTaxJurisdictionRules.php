<?php

declare(strict_types=1);

namespace FinGather\Service\Tax\Jurisdiction;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;

final readonly class SlovakiaTaxJurisdictionRules implements TaxJurisdictionRulesInterface
{
	private const int LongTermHoldingDays = 365;

	public function jurisdiction(): TaxJurisdictionEnum
	{
		return TaxJurisdictionEnum::Slovakia;
	}

	public function longTermHoldingDays(): int
	{
		return self::LongTermHoldingDays;
	}

	public function isLongTermHolding(int $holdingDays): bool
	{
		return $holdingDays >= self::LongTermHoldingDays;
	}

	public function isLossDeductible(int $holdingDays): bool
	{
		return $holdingDays < self::LongTermHoldingDays;
	}

	/** @return non-empty-list<CostBasisMethodEnum> */
	public function allowedCostBasisMethods(): array
	{
		return [CostBasisMethodEnum::Fifo, CostBasisMethodEnum::AverageCost];
	}

	public function defaultEstimatedTaxRate(): Decimal
	{
		return new Decimal('0.19');
	}

	public function annualGrossProceedsExemption(): ?Decimal
	{
		return null;
	}

	public function annualGainExemption(): Decimal
	{
		return new Decimal('500');
	}
}
