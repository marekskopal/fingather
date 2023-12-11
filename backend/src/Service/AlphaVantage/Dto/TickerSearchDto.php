<?php

declare(strict_types=1);

namespace FinGather\Service\AlphaVantage\Dto;

readonly class TickerSearchDto
{
	public function __construct(
		public string $symbol,
		public string $name,
		public string $type,
		public string $region,
		public string $marketOpen,
		public string $marketClose,
		public string $timezone,
		public string $currency,
		public float $matchScore,
	) {
	}
}
