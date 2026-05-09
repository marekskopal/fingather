<?php

declare(strict_types=1);

namespace FinGather\Service\Tax\Jurisdiction;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;

interface TaxJurisdictionRulesInterface
{
	public function jurisdiction(): TaxJurisdictionEnum;

	/** Days needed to qualify for long-term holding, or null if no such concept. */
	public function longTermHoldingDays(): ?int;

	/** Long-term holding (gains tax-exempt). */
	public function isLongTermHolding(int $holdingDays): bool;

	/** Whether realized losses are tax-deductible for a position with this holding period. */
	public function isLossDeductible(int $holdingDays): bool;

	/**
	 * Cost-basis methods legally allowed for the official tax report.
	 * First entry is the jurisdiction default.
	 *
	 * @return non-empty-list<CostBasisMethodEnum>
	 */
	public function allowedCostBasisMethods(): array;

	/** Headline tax rate used for "estimated savings" calculations; null when unknown / not applicable. */
	public function defaultEstimatedTaxRate(): ?Decimal;
}
