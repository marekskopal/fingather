<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerFundamental;

interface TickerFundamentalProviderInterface
{
	public function getTickerFundamental(Ticker $ticker): ?TickerFundamental;

	public function createTickerFundamental(Ticker $ticker): void;

	public function updateTickerFundamental(TickerFundamental $tickerFundamental): TickerFundamental;
}
