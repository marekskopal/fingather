<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainTransactionDto;

final readonly class McpTaxRealizedTransactionDto
{
	public function __construct(
		public string $ticker,
		public string $name,
		public string $buyDate,
		public string $sellDate,
		public int $holdingPeriodDays,
		public string $units,
		public string $buyPrice,
		public string $sellPrice,
		public string $costBasis,
		public string $salesProceeds,
		public string $fee,
		public string $gainLoss,
	) {
	}

	public static function fromDto(TaxReportRealizedGainTransactionDto $tx): self
	{
		return new self(
			ticker: $tx->tickerTicker,
			name: $tx->tickerName,
			buyDate: $tx->buyDate,
			sellDate: $tx->sellDate,
			holdingPeriodDays: $tx->holdingPeriodDays,
			units: (string) $tx->units,
			buyPrice: (string) $tx->buyPrice,
			sellPrice: (string) $tx->sellPrice,
			costBasis: (string) $tx->costBasis,
			salesProceeds: (string) $tx->salesProceeds,
			fee: (string) $tx->fee,
			gainLoss: (string) $tx->gainLoss,
		);
	}
}
