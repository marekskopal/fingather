<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select;
use FinGather\Model\Entity\Ticker;

/** @extends ARepository<Ticker> */
final class TickerRepository extends ARepository
{
	/** @return list<Ticker> */
	public function findTickers(?int $marketId = null, ?string $search = null, ?int $limit = null, ?int $offset = null,): iterable
	{
		return $this->getTickersSelect($marketId, $search, $limit, $offset)->fetchAll();
	}

	/** @return list<string> */
	public function findTickersTicker(?int $marketId = null, ?string $search = null, ?int $limit = null, ?int $offset = null): iterable
	{
		return $this->getTickersSelect($marketId, $search, $limit, $offset)->buildQuery()->columns(['ticker'])->fetchAll();
	}

	/** @return Select<Ticker> */
	private function getTickersSelect(?int $marketId = null, ?string $search = null, ?int $limit = null, ?int $offset = null): Select
	{
		$tickersSelect = $this->select();

		if ($marketId !== null) {
			$tickersSelect->where('market_id', $marketId);
		}

		if ($search !== null) {
			$tickersSelect->where('ticker', 'like', $search . '%');
		}

		if ($limit !== null) {
			$tickersSelect->limit($limit);
		}

		if ($offset !== null) {
			$tickersSelect->offset($offset);
		}

		$tickersSelect->orderBy('ticker');
		$tickersSelect->orderBy('market_id');

		return $tickersSelect;
	}

	public function findTicker(int $tickerId): ?Ticker
	{
		return $this->findOne([
			'id' => $tickerId,
		]);
	}

	/** @return list<Ticker> */
	public function findActiveTickers(): iterable
	{
		$activeTickersSelect = $this->orm->getSource(Ticker::class)
			->getDatabase()
			->select('ticker_id')
			->from('assets')
			->groupBy('ticker_id');

		return $this->select()
			->where('id', 'in', $activeTickersSelect)
			->fetchAll();
	}

	/** @return list<Ticker> */
	public function findTickersByTicker(string $ticker, ?int $marketId = null, ?string $isin = null): iterable
	{
		return $this->getTickerByTickerSelect($ticker, $marketId, $isin)
			->fetchAll();
	}

	public function countTickersByTicker(string $ticker, ?int $marketId = null, ?string $isin = null): int
	{
		return $this->getTickerByTickerSelect($ticker, $marketId, $isin)
			->count();
	}

	public function findTickerByTicker(string $ticker, ?int $marketId = null, ?string $isin = null): ?Ticker
	{
		return $this->getTickerByTickerSelect($ticker, $marketId, $isin)
			->fetchOne();
	}

	/** @return Select<Ticker> */
	private function getTickerByTickerSelect(string $ticker, ?int $marketId = null, ?string $isin = null): Select
	{
		$tickerSelect = $this->select()
			->where('ticker', $ticker);

		if ($marketId !== null) {
			$tickerSelect->where('market_id', $marketId);
		}

		if ($isin !== null) {
			$tickerSelect->where('isin', $isin);
		}

		return $tickerSelect;
	}

	/** @return list<Ticker> */
	public function findTickersByIsin(string $isin): iterable
	{
		return $this->getTickerByIsinSelect($isin)
			->fetchAll();
	}

	public function countTickersByIsin(string $isin): int
	{
		return $this->getTickerByIsinSelect($isin)
			->count();
	}

	public function findTickerByIsin(string $isin): ?Ticker
	{
		return $this->getTickerByIsinSelect($isin)
			->fetchOne();
	}

	/** @return Select<Ticker> */
	private function getTickerByIsinSelect(string $isin): Select
	{
		return $this->select()
			->where('isin', $isin);
	}
}
