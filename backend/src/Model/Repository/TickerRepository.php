<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Ticker;
use MarekSkopal\ORM\Query\Enum\DirectionEnum;
use MarekSkopal\ORM\Query\Select;
use MarekSkopal\ORM\Query\Where\WhereBuilder;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Ticker> */
final class TickerRepository extends AbstractRepository
{
	/** @return \Iterator<Ticker> */
	public function findTickers(?int $marketId = null, ?string $search = null, ?int $limit = null, ?int $offset = null,): \Iterator
	{
		return $this->getTickersSelect($marketId, $search, $limit, $offset)->fetchAll();
	}

	/** @return list<string> */
	public function findTickersTicker(?int $marketId = null, ?string $search = null, ?int $limit = null, ?int $offset = null): array
	{
		/**
		 * @var \Iterator<array{
		 *     ticker: string,
		 * }> $tickers
		 */
		$tickers = $this->getTickersSelect($marketId, $search, $limit, $offset)->columns(['ticker'])->fetchAssocAll();
		return array_map(fn(array $ticker): string => $ticker['ticker'], iterator_to_array($tickers, false));
	}

	/** @return Select<Ticker> */
	private function getTickersSelect(?int $marketId = null, ?string $search = null, ?int $limit = null, ?int $offset = null): Select
	{
		$tickersSelect = $this->select();

		if ($marketId !== null) {
			$tickersSelect->where(['market_id' => $marketId]);
		}

		if ($search !== null) {
			$tickersSelect->where(
				fn (WhereBuilder $builder): WhereBuilder =>
					$builder->where(['name', 'like', $search . '%'])
					->orWhere(['ticker', 'like', $search . '%']),
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

	/** @return \Iterator<Ticker> */
	public function findActiveTickers(): \Iterator
	{
		$activeTickersSelect = $this->queryProvider
			->select(Asset::class)
			->columns(['ticker_id'])
			->groupBy(['ticker_id']);

		return $this->select()
			->where(['id', 'in', $activeTickersSelect])
			->fetchAll();
	}

	/** @return list<Ticker> */
	public function findTickersMostUsed(?int $limit = null, ?int $offset = null): array
	{
		$mostUsedTickersSelect = $this->queryProvider
			->select(Asset::class)
			->columns(['ticker_id'])
			->groupBy(['ticker_id'])
			->orderBy('count(*)', DirectionEnum::Desc)
			->limit($limit)
			->offset($offset)
			->fetchAssocAll();

		/** @var list<int> $mostUsedTickerIds */
		$mostUsedTickerIds = array_column(iterator_to_array($mostUsedTickersSelect, false), 'ticker_id');

		$tickers = iterator_to_array($this->select()
			->where(['id', 'in', $mostUsedTickerIds])
			->fetchAll(), false);

		usort(
			$tickers,
			fn (Ticker $a, Ticker $b): int =>
				array_search(
					$a->getId(),
					$mostUsedTickerIds,
					true,
				) <=> array_search(
					$b->getId(),
					$mostUsedTickerIds,
					true,
				),
		);

		return $tickers;
	}

	/**
	 * @param list<int>|null $marketIds
	 * @return \Iterator<Ticker>
	 */
	public function findTickersByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): \Iterator
	{
		return $this->getTickerByTickerSelect($ticker, $marketIds, $isin)
			->fetchAll();
	}

	/** @param list<int>|null $marketIds */
	public function countTickersByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): int
	{
		return $this->getTickerByTickerSelect($ticker, $marketIds, $isin)
			->count();
	}

	/** @param list<int>|null $marketIds */
	public function findTickerByTicker(string $ticker, ?array $marketIds = null, ?string $isin = null): ?Ticker
	{
		return $this->getTickerByTickerSelect($ticker, $marketIds, $isin)
			->fetchOne();
	}

	/**
	 * @param list<int>|null $marketIds
	 * @return Select<Ticker>
	 */
	private function getTickerByTickerSelect(string $ticker, ?array $marketIds = null, ?string $isin = null): Select
	{
		$tickerSelect = $this->select()
			->where(['ticker' => $ticker]);

		if ($marketIds !== null) {
			$tickerSelect->where(['market_id', 'in', $marketIds]);
		}

		if ($isin !== null) {
			$tickerSelect->where(['isin' => $isin]);
		}

		return $tickerSelect;
	}

	/**
	 * @param list<int>|null $marketIds
	 * @return \Iterator<Ticker>
	 */
	public function findTickersByIsin(string $isin, ?array $marketIds = null): \Iterator
	{
		return $this->getTickerByIsinSelect($isin, $marketIds)
			->fetchAll();
	}

	/** @param list<int>|null $marketIds */
	public function countTickersByIsin(string $isin, ?array $marketIds = null): int
	{
		return $this->getTickerByIsinSelect($isin, $marketIds)
			->count();
	}

	/** @param list<int>|null $marketIds */
	public function findTickerByIsin(string $isin, ?array $marketIds = null): ?Ticker
	{
		return $this->getTickerByIsinSelect($isin, $marketIds)
			->fetchOne();
	}

	/**
	 * @param list<int>|null $marketIds
	 * @return Select<Ticker>
	 */
	private function getTickerByIsinSelect(string $isin, ?array $marketIds = null): Select
	{
		$tickerSelect = $this->select()
			->where(['isin' => $isin]);

		if ($marketIds !== null) {
			$tickerSelect->where(['market_id', 'in', $marketIds]);
		}

		return $tickerSelect;
	}
}
