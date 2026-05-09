<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Tax\Jurisdiction;

use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Service\Tax\Jurisdiction\CzechRepublicTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\GenericTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\TaxJurisdictionRulesFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxJurisdictionRulesFactory::class)]
#[UsesClass(CzechRepublicTaxJurisdictionRules::class)]
#[UsesClass(GenericTaxJurisdictionRules::class)]
final class TaxJurisdictionRulesFactoryTest extends TestCase
{
	public function testForJurisdictionCzechRepublic(): void
	{
		$factory = $this->factory();
		$rules = $factory->forJurisdiction(TaxJurisdictionEnum::CzechRepublic);

		self::assertSame(TaxJurisdictionEnum::CzechRepublic, $rules->jurisdiction());
	}

	public function testForJurisdictionGeneric(): void
	{
		$factory = $this->factory();
		$rules = $factory->forJurisdiction(TaxJurisdictionEnum::Generic);

		self::assertSame(TaxJurisdictionEnum::Generic, $rules->jurisdiction());
	}

	private function factory(): TaxJurisdictionRulesFactory
	{
		return new TaxJurisdictionRulesFactory(
			new CzechRepublicTaxJurisdictionRules(),
			new GenericTaxJurisdictionRules(),
		);
	}
}
