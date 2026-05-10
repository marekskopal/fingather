<?php

declare(strict_types=1);

namespace FinGather\Service\Tax\Jurisdiction;

use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Model\Entity\Portfolio;

final readonly class TaxJurisdictionRulesFactory
{
	public function __construct(
		private CzechRepublicTaxJurisdictionRules $czechRepublic,
		private SlovakiaTaxJurisdictionRules $slovakia,
		private GermanyTaxJurisdictionRules $germany,
		private GenericTaxJurisdictionRules $generic,
	) {
	}

	public function forPortfolio(Portfolio $portfolio): TaxJurisdictionRulesInterface
	{
		return $this->forJurisdiction($portfolio->taxJurisdiction);
	}

	public function forJurisdiction(TaxJurisdictionEnum $jurisdiction): TaxJurisdictionRulesInterface
	{
		return match ($jurisdiction) {
			TaxJurisdictionEnum::CzechRepublic => $this->czechRepublic,
			TaxJurisdictionEnum::Slovakia => $this->slovakia,
			TaxJurisdictionEnum::Germany => $this->germany,
			TaxJurisdictionEnum::Generic => $this->generic,
		};
	}
}
