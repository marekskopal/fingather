<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Ticker;
use Iterator;

interface TickerProviderInterface
{
	/** @return Iterator<Ticker> */
	public function getTickers(?Market $market = null, ?string $search = null, ?int $limit = null, ?int $offset = null): Iterator;

	public function getTicker(int $tickerId): ?Ticker;

	/** @return Iterator<Ticker> */
	public function getActiveTickers(): Iterator;

	/** @return list<Ticker> */
	public function getTickersMostUsed(?int $limit = null, ?int $offset = null): array;

	/**
	 * @param list<int>|null $marketIds
	 * @return Iterator<Ticker>
	 */
	public function getTickersByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): Iterator;

	/** @param list<int>|null $marketIds */
	public function countTickersByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): int;

	/** @param list<int>|null $marketIds */
	public function getTickerByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): ?Ticker;

	/**
	 * @param list<int>|null $marketIds
	 * @return Iterator<Ticker>
	 */
	public function getTickersByIsin(string $isin, ?array $marketIds = null): Iterator;

	/** @param list<int>|null $marketIds */
	public function countTickersByIsin(string $isin, ?array $marketIds = null): int;

	/** @param list<int>|null $marketIds */
	public function getTickerByIsin(string $isin, ?array $marketIds = null): ?Ticker;
}
