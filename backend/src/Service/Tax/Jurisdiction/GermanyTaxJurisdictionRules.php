<?php

declare(strict_types=1);

namespace FinGather\Service\Tax\Jurisdiction;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;

final readonly class GermanyTaxJurisdictionRules implements TaxJurisdictionRulesInterface
{
	public function jurisdiction(): TaxJurisdictionEnum
	{
		return TaxJurisdictionEnum::Germany;
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

	/** @return non-empty-list<CostBasisMethodEnum> */
	public function allowedCostBasisMethods(): array
	{
		return [CostBasisMethodEnum::Fifo];
	}

	public function defaultEstimatedTaxRate(): Decimal
	{
		// 25% Abgeltungsteuer + 5.5% solidarity surcharge.
		return new Decimal('0.26375');
	}

	public function annualGrossProceedsExemption(): ?Decimal
	{
		return null;
	}

	public function annualGainExemption(): Decimal
	{
		// Sparerpauschbetrag (single filer).
		return new Decimal('1000');
	}
}
