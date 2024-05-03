<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Ticker;
use FinGather\Service\Import\Entity\PrepareImportTicker;

final readonly class ImportPrepareTickerDto
{
	/** @param list<TickerDto> $tickers */
	public function __construct(public int $brokerId, public string $ticker, public array $tickers)
	{
	}

	public static function fromImportPrepareTicker(PrepareImportTicker $prepareImportTicker): self
	{
		return new self(
			brokerId: $prepareImportTicker->brokerId,
			ticker: $prepareImportTicker->ticker,
			tickers: array_map(
				fn (Ticker $ticker) => TickerDto::fromEntity($ticker),
				$prepareImportTicker->tickers,
			),
		);
	}
}
