<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Tax\Jurisdiction;

use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Service\Tax\Jurisdiction\CzechRepublicTaxJurisdictionRules;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CzechRepublicTaxJurisdictionRules::class)]
final class CzechRepublicTaxJurisdictionRulesTest extends TestCase
{
	public function testJurisdiction(): void
	{
		$rules = new CzechRepublicTaxJurisdictionRules();
		self::assertSame(TaxJurisdictionEnum::CzechRepublic, $rules->jurisdiction());
	}

	public function testLongTermHoldingDays(): void
	{
		$rules = new CzechRepublicTaxJurisdictionRules();
		self::assertSame(1095, $rules->longTermHoldingDays());
	}

	public function testIsLongTermHoldingBoundary(): void
	{
		$rules = new CzechRepublicTaxJurisdictionRules();
		self::assertFalse($rules->isLongTermHolding(1094));
		self::assertTrue($rules->isLongTermHolding(1095));
		self::assertTrue($rules->isLongTermHolding(2000));
	}

	public function testIsLossDeductibleSymmetricBoundary(): void
	{
		$rules = new CzechRepublicTaxJurisdictionRules();
		self::assertTrue($rules->isLossDeductible(0));
		self::assertTrue($rules->isLossDeductible(1094));
		self::assertFalse($rules->isLossDeductible(1095));
		self::assertFalse($rules->isLossDeductible(2000));
	}

	public function testAllowedCostBasisMethodsExcludeLifo(): void
	{
		$rules = new CzechRepublicTaxJurisdictionRules();
		$allowed = $rules->allowedCostBasisMethods();

		self::assertContains(CostBasisMethodEnum::Fifo, $allowed);
		self::assertContains(CostBasisMethodEnum::AverageCost, $allowed);
		self::assertNotContains(CostBasisMethodEnum::Lifo, $allowed);
		self::assertSame(CostBasisMethodEnum::Fifo, $allowed[0]);
	}

	public function testDefaultEstimatedTaxRate(): void
	{
		$rules = new CzechRepublicTaxJurisdictionRules();

		self::assertSame(0.15, $rules->defaultEstimatedTaxRate()->toFloat());
	}
}
