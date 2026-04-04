<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dcf\Dto;

use Decimal\Decimal;

final readonly class DcfInputs
{
	/** @param list<DcfHistoryPointDto> $history newest→oldest */
	public function __construct(
		public int $sharesOutstanding,
		public ?int $latestRevenue,
		public ?int $latestFcfe,
		public ?float $quarterlyRevenueGrowth,
		public ?float $beta,
		public array $history,
		public ?Decimal $currentPrice,
	) {
	}
}
