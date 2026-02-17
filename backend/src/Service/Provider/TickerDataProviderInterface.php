<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Service\Provider\Dto\TickerDataAdjustedDto;
use Iterator;

interface TickerDataProviderInterface
{
	/** @return Iterator<TickerData> */
	public function getTickerDatas(Ticker $ticker, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): Iterator;

	/** @return list<TickerDataAdjustedDto> */
	public function getAdjustedTickerDatas(Ticker $ticker, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): array;

	public function getLastTickerDataClose(Ticker $ticker, DateTimeImmutable $beforeDate): ?Decimal;

	public function updateTickerData(Ticker $ticker, bool $fullHistory = false): ?DateTimeImmutable;
}
