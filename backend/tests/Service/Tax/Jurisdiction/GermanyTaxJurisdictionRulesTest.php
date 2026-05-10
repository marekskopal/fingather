<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Tax\Jurisdiction;

use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Service\Tax\Jurisdiction\GermanyTaxJurisdictionRules;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GermanyTaxJurisdictionRules::class)]
final class GermanyTaxJurisdictionRulesTest extends TestCase
{
	public function testJurisdiction(): void
	{
		$rules = new GermanyTaxJurisdictionRules();
		self::assertSame(TaxJurisdictionEnum::Germany, $rules->jurisdiction());
	}

	public function testNoLongTermConcept(): void
	{
		$rules = new GermanyTaxJurisdictionRules();
		self::assertNull($rules->longTermHoldingDays());
		self::assertFalse($rules->isLongTermHolding(0));
		self::assertFalse($rules->isLongTermHolding(10000));
	}

	public function testLossesAlwaysDeductible(): void
	{
		$rules = new GermanyTaxJurisdictionRules();
		self::assertTrue($rules->isLossDeductible(0));
		self::assertTrue($rules->isLossDeductible(10000));
	}

	public function testOnlyFifoAllowed(): void
	{
		$rules = new GermanyTaxJurisdictionRules();
		$allowed = $rules->allowedCostBasisMethods();

		self::assertSame([CostBasisMethodEnum::Fifo], $allowed);
	}

	public function testDefaultEstimatedTaxRate(): void
	{
		$rules = new GermanyTaxJurisdictionRules();

		self::assertSame(0.26375, $rules->defaultEstimatedTaxRate()->toFloat());
	}

	public function testAnnualGainExemption(): void
	{
		$rules = new GermanyTaxJurisdictionRules();

		self::assertSame(1000.0, $rules->annualGainExemption()->toFloat());
	}

	public function testNoAnnualGrossProceedsExemption(): void
	{
		$rules = new GermanyTaxJurisdictionRules();

		self::assertNull($rules->annualGrossProceedsExemption());
	}
}
