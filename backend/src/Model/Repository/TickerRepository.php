<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select;
use Cycle\ORM\Select\QueryBuilder;
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
	public function findTickersTicker(?int $marketId = null, ?string $search = null, ?int $limit = null, ?int $offset = null): array
	{
		/**
		 * @var list<array{
		 *     ticker: string,
		 * }> $tickers
		 */
		$tickers = iterator_to_array(
			$this->getTickersSelect($marketId, $search, $limit, $offset)->buildQuery()->columns(['ticker'])->fetchAll(),
		);
		return array_map(fn(array $ticker): string => $ticker['ticker'], $tickers);
	}

	/** @return Select<Ticker> */
	private function getTickersSelect(?int $marketId = null, ?string $search = null, ?int $limit = null, ?int $offset = null): Select
	{
		$tickersSelect = $this->select();

		if ($marketId !== null) {
			$tickersSelect->where('market_id', $marketId);
		}

		if ($search !== null) {
			$tickersSelect->where(
				fn (QueryBuilder $select) =>
					$select->where('name', 'like', $search . '%')
					->orWhere('ticker', 'like', $search . '%'),
			);
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

	/**
	 * @param list<int> $marketIds
	 * @return list<Ticker>
	 */
	public function findTickersByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): iterable
	{
		return $this->getTickerByTickerSelect($ticker, $marketIds, $isin)
			->fetchAll();
	}

	/** @param list<int> $marketIds */
	public function countTickersByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): int
	{
		return $this->getTickerByTickerSelect($ticker, $marketIds, $isin)
			->count();
	}

	/** @param list<int> $marketIds */
	public function findTickerByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): ?Ticker
	{
		return $this->getTickerByTickerSelect($ticker, $marketIds, $isin)
			->fetchOne();
	}

	/**
	 * @param list<int> $marketIds
	 * @return Select<Ticker>
	 */
	private function getTickerByTickerSelect(string $ticker, ?array $marketIds = null, ?string $isin = null): Select
	{
		$tickerSelect = $this->select()
			->where('ticker', $ticker);

		if ($marketIds !== null) {
			$tickerSelect->where('market_id', 'in', $marketIds);
		}

		if ($isin !== null) {
			$tickerSelect->where('isin', $isin);
		}

		return $tickerSelect;
	}

	/**
	 * @param list<int> $marketIds
	 * @return list<Ticker>
	 */
	public function findTickersByIsin(string $isin, ?array $marketIds = null): iterable
	{
		return $this->getTickerByIsinSelect($isin, $marketIds)
			->fetchAll();
	}

	/** @param list<int> $marketIds */
	public function countTickersByIsin(string $isin, ?array $marketIds = null): int
	{
		return $this->getTickerByIsinSelect($isin, $marketIds)
			->count();
	}

	/** @param list<int> $marketIds */
	public function findTickerByIsin(string $isin, ?array $marketIds = null): ?Ticker
	{
		return $this->getTickerByIsinSelect($isin, $marketIds)
			->fetchOne();
	}

	/**
	 * @param list<int> $marketIds
	 * @return Select<Ticker>
	 */
	private function getTickerByIsinSelect(string $isin, ?array $marketIds = null): Select
	{
		$tickerSelect = $this->select()
			->where('isin', $isin);

		if ($marketIds !== null) {
			$tickerSelect->where('market_id', 'in', $marketIds);
		}

		return $tickerSelect;
	}
}
