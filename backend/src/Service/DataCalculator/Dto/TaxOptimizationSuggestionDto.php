<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final readonly class TaxOptimizationSuggestionDto
{
	public function __construct(
		public int $assetId,
		public string $tickerTicker,
		public string $tickerName,
		public ?string $tickerLogo,
		public string $firstBuyDate,
		public int $holdingPeriodDays,
		public ?int $daysUntilLongTerm,
		public Decimal $units,
		public Decimal $marketValue,
		public Decimal $costBasis,
		public Decimal $unrealizedGainLoss,
		public ?Decimal $estimatedTaxImpact,
		public TaxOptimizationRationaleEnum $rationale,
		public bool $holdingVariesByBroker,
	) {
	}
}
