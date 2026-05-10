<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Tax\Jurisdiction;

use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Service\Tax\Jurisdiction\GenericTaxJurisdictionRules;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GenericTaxJurisdictionRules::class)]
final class GenericTaxJurisdictionRulesTest extends TestCase
{
	public function testJurisdiction(): void
	{
		$rules = new GenericTaxJurisdictionRules();
		self::assertSame(TaxJurisdictionEnum::Generic, $rules->jurisdiction());
	}

	public function testNoLongTermConcept(): void
	{
		$rules = new GenericTaxJurisdictionRules();
		self::assertNull($rules->longTermHoldingDays());
		self::assertFalse($rules->isLongTermHolding(0));
		self::assertFalse($rules->isLongTermHolding(10000));
	}

	public function testLossesAlwaysDeductible(): void
	{
		$rules = new GenericTaxJurisdictionRules();
		self::assertTrue($rules->isLossDeductible(0));
		self::assertTrue($rules->isLossDeductible(10000));
	}

	public function testAllowedCostBasisMethodsIncludeAll(): void
	{
		$rules = new GenericTaxJurisdictionRules();
		$allowed = $rules->allowedCostBasisMethods();

		self::assertContains(CostBasisMethodEnum::Fifo, $allowed);
		self::assertContains(CostBasisMethodEnum::Lifo, $allowed);
		self::assertContains(CostBasisMethodEnum::AverageCost, $allowed);
	}

	public function testDefaultEstimatedTaxRateIsNull(): void
	{
		$rules = new GenericTaxJurisdictionRules();
		self::assertNull($rules->defaultEstimatedTaxRate());
	}

	public function testNoAllowances(): void
	{
		$rules = new GenericTaxJurisdictionRules();
		self::assertNull($rules->annualGrossProceedsExemption());
		self::assertNull($rules->annualGainExemption());
	}
}
