<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Tax\Jurisdiction;

use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Service\Tax\Jurisdiction\CzechRepublicTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\GenericTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\GermanyTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\SlovakiaTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\TaxJurisdictionRulesFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxJurisdictionRulesFactory::class)]
#[UsesClass(CzechRepublicTaxJurisdictionRules::class)]
#[UsesClass(SlovakiaTaxJurisdictionRules::class)]
#[UsesClass(GermanyTaxJurisdictionRules::class)]
#[UsesClass(GenericTaxJurisdictionRules::class)]
final class TaxJurisdictionRulesFactoryTest extends TestCase
{
	public function testForJurisdictionCzechRepublic(): void
	{
		$rules = $this->factory()->forJurisdiction(TaxJurisdictionEnum::CzechRepublic);

		self::assertSame(TaxJurisdictionEnum::CzechRepublic, $rules->jurisdiction());
	}

	public function testForJurisdictionSlovakia(): void
	{
		$rules = $this->factory()->forJurisdiction(TaxJurisdictionEnum::Slovakia);

		self::assertSame(TaxJurisdictionEnum::Slovakia, $rules->jurisdiction());
	}

	public function testForJurisdictionGermany(): void
	{
		$rules = $this->factory()->forJurisdiction(TaxJurisdictionEnum::Germany);

		self::assertSame(TaxJurisdictionEnum::Germany, $rules->jurisdiction());
	}

	public function testForJurisdictionGeneric(): void
	{
		$rules = $this->factory()->forJurisdiction(TaxJurisdictionEnum::Generic);

		self::assertSame(TaxJurisdictionEnum::Generic, $rules->jurisdiction());
	}

	private function factory(): TaxJurisdictionRulesFactory
	{
		return new TaxJurisdictionRulesFactory(
			new CzechRepublicTaxJurisdictionRules(),
			new SlovakiaTaxJurisdictionRules(),
			new GermanyTaxJurisdictionRules(),
			new GenericTaxJurisdictionRules(),
		);
	}
}
