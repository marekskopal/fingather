<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

use Decimal\Decimal;

class DividendDataAssetDto
{
	public function __construct(
		public readonly int $id,
		public readonly string $tickerTicker,
		public readonly string $tickerName,
		public Decimal $dividendGain,
	) {
	}
}
