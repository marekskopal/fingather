<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Entity;

use FinGather\Model\Entity\Ticker;

readonly class PrepareImportTicker
{
	/** @param list<Ticker> $tickers */
	public function __construct(public string $ticker, public array $tickers)
	{
	}
}
