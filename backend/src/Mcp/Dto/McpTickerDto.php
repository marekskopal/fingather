<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Ticker;

final readonly class McpTickerDto
{
	public function __construct(
		public int $tickerId,
		public string $ticker,
		public string $name,
		public string $type,
		public string $market,
		public string $currency,
		public ?string $isin,
		public ?string $sector,
		public ?string $country,
	) {
	}

	public static function fromEntity(Ticker $ticker): self
	{
		return new self(
			tickerId: $ticker->id,
			ticker: $ticker->ticker,
			name: $ticker->name,
			type: $ticker->type->value,
			market: $ticker->market->name,
			currency: $ticker->currency->code,
			isin: $ticker->isin,
			sector: $ticker->sector->name,
			country: $ticker->country->name,
		);
	}
}
