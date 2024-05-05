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

	/** @return array<Ticker> */
	public function getTickers(?Market $market = null, ?string $search = null, ?int $limit = null, ?int $offset = null,): array
	{
		return $this->tickerRepository->findTickers(marketId: $market?->getId(), search: $search, limit: $limit, offset: $offset);
	}

	public function getTicker(int $tickerId): ?Ticker
	{
		return $this->tickerRepository->findTicker($tickerId);
	}

	/** @return array<Ticker> */
	public function getActiveTickers(): array
	{
		return $this->tickerRepository->findActiveTickers();
	}

	/** @return array<Ticker> */
	public function getTickersByTicker(string $ticker, ?Market $market = null, ?string $isin = null): array
	{
		return $this->tickerRepository->findTickersByTicker($ticker, $market?->getId(), $isin);
	}

	public function countTickersByTicker(string $ticker, ?Market $market = null, ?string $isin = null): int
	{
		return $this->tickerRepository->countTickersByTicker($ticker, $market?->getId(), $isin);
	}

	public function getTickerByTicker(string $ticker, ?Market $market = null, ?string $isin = null): ?Ticker
	{
		return $this->tickerRepository->findTickerByTicker($ticker, $market?->getId(), $isin);
	}

	/** @return array<Ticker> */
	public function getTickersByIsin(string $isin): array
	{
		return $this->tickerRepository->findTickersByIsin($isin);
	}

	public function countTickersByIsin(string $isin): int
	{
		return $this->tickerRepository->countTickersByIsin($isin);
	}

	public function getTickerByIsin(string $isin): ?Ticker
	{
		return $this->tickerRepository->findTickerByIsin($isin);
	}
}
