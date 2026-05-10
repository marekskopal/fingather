<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Service\Tax\Jurisdiction\TaxJurisdictionRulesInterface;

final readonly class PortfolioTaxSettingsDto
{
	/** @param non-empty-list<CostBasisMethodEnum> $allowedCostBasisMethods */
	public function __construct(
		public int $portfolioId,
		public TaxJurisdictionEnum $taxJurisdiction,
		public CostBasisMethodEnum $costBasisMethod,
		public ?Decimal $estimatedTaxRate,
		public ?int $longTermHoldingDays,
		public ?Decimal $defaultEstimatedTaxRate,
		public ?Decimal $annualGainExemption,
		public ?Decimal $annualGrossProceedsExemption,
		public array $allowedCostBasisMethods,
	) {
	}

	public static function fromEntity(Portfolio $portfolio, TaxJurisdictionRulesInterface $rules): self
	{
		return new self(
			portfolioId: $portfolio->id,
			taxJurisdiction: $portfolio->taxJurisdiction,
			costBasisMethod: $portfolio->costBasisMethod,
			estimatedTaxRate: $portfolio->estimatedTaxRate,
			longTermHoldingDays: $rules->longTermHoldingDays(),
			defaultEstimatedTaxRate: $rules->defaultEstimatedTaxRate(),
			annualGainExemption: $rules->annualGainExemption(),
			annualGrossProceedsExemption: $rules->annualGrossProceedsExemption(),
			allowedCostBasisMethods: $rules->allowedCostBasisMethods(),
		);
	}
}
