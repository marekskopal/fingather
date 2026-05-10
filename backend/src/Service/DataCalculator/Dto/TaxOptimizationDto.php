<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;

final readonly class TaxOptimizationDto
{
	/**
	 * @param list<TaxOptimizationSuggestionDto> $harvestNow
	 * @param list<TaxOptimizationSuggestionDto> $holdForTaxFreeGain
	 * @param list<TaxOptimizationSuggestionDto> $lossNoLongerDeductible
	 * @param list<TaxOptimizationSuggestionDto> $alreadyTaxFree
	 * @param list<TaxOptimizationSuggestionDto> $winningShortTerm
	 */
	public function __construct(
		public string $asOfDate,
		public TaxJurisdictionEnum $jurisdiction,
		public ?int $longTermHoldingDays,
		public ?Decimal $estimatedTaxRate,
		public ?Decimal $annualGainExemption,
		public ?Decimal $annualGrossProceedsExemption,
		public array $harvestNow,
		public array $holdForTaxFreeGain,
		public array $lossNoLongerDeductible,
		public array $alreadyTaxFree,
		public array $winningShortTerm,
		public Decimal $estimatedTaxSavedByHarvestingNow,
		public Decimal $estimatedTaxSavedByWaiting,
	) {
	}
}
