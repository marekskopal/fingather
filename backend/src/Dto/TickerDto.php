<?php

namespace FinGather\Dto;

final readonly class TickerDto
{
	public function __construct(
		public int $id,
		public string $ticker,
		public string $name,
		public int $marketId,
		public MarketDto $market,
	) {
	}
}
