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

	/**
	 * Annual exemption on the GROSS PROCEEDS from securities sales (e.g. CZ 100,000 CZK).
	 * If total gross proceeds in a tax year stay at or below this amount, gains are entirely exempt.
	 * Null when the jurisdiction has no such allowance.
	 */
	public function annualGrossProceedsExemption(): ?Decimal;

	/**
	 * Annual exemption on the NET GAIN from securities (e.g. SK €500, DE €1,000 Sparerpauschbetrag).
	 * Applied after the gross-proceeds exemption short-circuit, before computing tax.
	 * Null when the jurisdiction has no such allowance.
	 */
	public function annualGainExemption(): ?Decimal;
}
