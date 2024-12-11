<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\TickerRepository;

class TickerProvider
{
	public function __construct(private readonly TickerRepository $tickerRepository,)
	{
	}

	/** @return \Iterator<Ticker> */
	public function getTickers(?Market $market = null, ?string $search = null, ?int $limit = null, ?int $offset = null,): \Iterator
	{
		return $this->tickerRepository->findTickers(marketId: $market?->getId(), search: $search, limit: $limit, offset: $offset);
	}

	public function getTicker(int $tickerId): ?Ticker
	{
		return $this->tickerRepository->findTicker($tickerId);
	}

	/** @return \Iterator<Ticker> */
	public function getActiveTickers(): \Iterator
	{
		return $this->tickerRepository->findActiveTickers();
	}

	/** @return list<Ticker> */
	public function getTickersMostUsed(?int $limit = null, ?int $offset = null,): array
	{
		return $this->tickerRepository->findTickersMostUsed(limit: $limit, offset: $offset);
	}

	/**
	 * @param list<int>|null $marketIds
	 * @return \Iterator<Ticker>
	 */
	public function getTickersByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): \Iterator
	{
		return $this->tickerRepository->findTickersByTicker($ticker, $marketIds, $isin);
	}

	/** @param list<int>|null $marketIds */
	public function countTickersByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): int
	{
		return $this->tickerRepository->countTickersByTicker($ticker, $marketIds, $isin);
	}

	/** @param list<int>|null $marketIds */
	public function getTickerByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): ?Ticker
	{
		return $this->tickerRepository->findTickerByTicker($ticker, $marketIds, $isin);
	}

	/**
	 * @param list<int>|null $marketIds
	 * @return \Iterator<Ticker>
	 */
	public function getTickersByIsin(string $isin, ?array $marketIds = null): \Iterator
	{
		return $this->tickerRepository->findTickersByIsin($isin, $marketIds);
	}

	/** @param list<int>|null $marketIds */
	public function countTickersByIsin(string $isin, ?array $marketIds = null): int
	{
		return $this->tickerRepository->countTickersByIsin($isin, $marketIds);
	}

	/** @param list<int>|null $marketIds */
	public function getTickerByIsin(string $isin, ?array $marketIds = null): ?Ticker
	{
		return $this->tickerRepository->findTickerByIsin($isin, $marketIds);
	}
}
