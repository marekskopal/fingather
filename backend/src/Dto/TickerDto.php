<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Ticker;

final readonly class TickerDto
{
	public function __construct(public int $id, public string $ticker, public string $name, public int $marketId, public MarketDto $market,)
	{
	}

	public static function fromEntity(Ticker $ticker): self
	{
		return new self(
			id: $ticker->getId(),
			ticker: $ticker->getTicker(),
			name: $ticker->getName(),
			marketId: $ticker->getMarket()->getId(),
			market: MarketDto::fromEntity($ticker->getMarket())
		);
	}
}
