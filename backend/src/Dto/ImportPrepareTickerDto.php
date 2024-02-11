<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Ticker;
use FinGather\Service\Import\Entity\PrepareImportTicker;

readonly class ImportPrepareTickerDto
{
	/** @param list<TickerDto> $tickers */
	public function __construct(public string $ticker, public array $tickers)
	{
	}

	public static function fromImportPrepareTicker(PrepareImportTicker $prepareImportTicker): self
	{
		return new self(
			ticker: $prepareImportTicker->ticker,
			tickers: array_map(
				fn (Ticker $ticker) => TickerDto::fromEntity($ticker),
				$prepareImportTicker->tickers,
			),
		);
	}
}
