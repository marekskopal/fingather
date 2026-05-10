<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Tax\Jurisdiction;

use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Service\Tax\Jurisdiction\SlovakiaTaxJurisdictionRules;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SlovakiaTaxJurisdictionRules::class)]
final class SlovakiaTaxJurisdictionRulesTest extends TestCase
{
	public function testJurisdiction(): void
	{
		$rules = new SlovakiaTaxJurisdictionRules();
		self::assertSame(TaxJurisdictionEnum::Slovakia, $rules->jurisdiction());
	}

	public function testLongTermHoldingDays(): void
	{
		$rules = new SlovakiaTaxJurisdictionRules();
		self::assertSame(365, $rules->longTermHoldingDays());
	}

	public function testIsLongTermHoldingBoundary(): void
	{
		$rules = new SlovakiaTaxJurisdictionRules();
		self::assertFalse($rules->isLongTermHolding(364));
		self::assertTrue($rules->isLongTermHolding(365));
		self::assertTrue($rules->isLongTermHolding(1000));
	}

	public function testIsLossDeductibleSymmetricBoundary(): void
	{
		$rules = new SlovakiaTaxJurisdictionRules();
		self::assertTrue($rules->isLossDeductible(0));
		self::assertTrue($rules->isLossDeductible(364));
		self::assertFalse($rules->isLossDeductible(365));
		self::assertFalse($rules->isLossDeductible(1000));
	}

	public function testAllowedCostBasisMethodsExcludeLifo(): void
	{
		$rules = new SlovakiaTaxJurisdictionRules();
		$allowed = $rules->allowedCostBasisMethods();

		self::assertContains(CostBasisMethodEnum::Fifo, $allowed);
		self::assertContains(CostBasisMethodEnum::AverageCost, $allowed);
		self::assertNotContains(CostBasisMethodEnum::Lifo, $allowed);
		self::assertSame(CostBasisMethodEnum::Fifo, $allowed[0]);
	}

	public function testDefaultEstimatedTaxRate(): void
	{
		$rules = new SlovakiaTaxJurisdictionRules();

		self::assertSame(0.19, $rules->defaultEstimatedTaxRate()->toFloat());
	}

	public function testAnnualGainExemption(): void
	{
		$rules = new SlovakiaTaxJurisdictionRules();

		self::assertSame(500.0, $rules->annualGainExemption()->toFloat());
	}

	public function testNoAnnualGrossProceedsExemption(): void
	{
		$rules = new SlovakiaTaxJurisdictionRules();

		self::assertNull($rules->annualGrossProceedsExemption());
	}
}
