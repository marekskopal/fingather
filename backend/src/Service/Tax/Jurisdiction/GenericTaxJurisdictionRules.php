<?php

declare(strict_types=1);

namespace FinGather\Service\Tax\Jurisdiction;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;

final readonly class GenericTaxJurisdictionRules implements TaxJurisdictionRulesInterface
{
	public function jurisdiction(): TaxJurisdictionEnum
	{
		return TaxJurisdictionEnum::Generic;
	}

	public function longTermHoldingDays(): ?int
	{
		return null;
	}

	public function isLongTermHolding(int $holdingDays): bool
	{
		return false;
	}

	public function isLossDeductible(int $holdingDays): bool
	{
		return true;
	}

	public function allowedCostBasisMethods(): array
	{
		return [CostBasisMethodEnum::Fifo, CostBasisMethodEnum::Lifo, CostBasisMethodEnum::AverageCost];
	}

	public function defaultEstimatedTaxRate(): ?Decimal
	{
		return null;
	}
}
