<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Ticker;

/** @extends ARepository<Ticker> */
class TickerRepository extends ARepository
{
	/** @return array<Ticker> */
	public function findTickers(?string $search = null, ?int $limit = null, ?int $offset = null,): array
	{
		$tickers = $this->select();

		if ($search !== null) {
			$tickers->where('ticker', 'like', $search . '%');
		}

		if ($limit !== null) {
			$tickers->limit($limit);
		}

		if ($offset !== null) {
			$tickers->offset($offset);
		}

		$tickers->orderBy('ticker', 'DESC');

		return $tickers->fetchAll();
	}

	public function findTicker(int $tickerId): ?Ticker
	{
		return $this->findOne([
			'id' => $tickerId,
		]);
	}

	/** @return array<Ticker> */
	public function findActiveTickers(): array
	{
		$activeTickersSelect = $this->orm->getSource(Ticker::class)
			->getDatabase()
			->select('ticker_id')
			->from('assets')
			->groupBy('ticker_id');

		return $this->select()
			->where('in', 'in', $activeTickersSelect)
			->fetchAll();
	}

	/** @return array<Ticker> */
	public function findTickersByTicker(string $ticker): array
	{
		return $this->select()
			->where('ticker', $ticker)
			->fetchAll();
	}

	public function countTickersByTicker(string $ticker): int
	{
		return $this->select()
			->where('ticker', $ticker)
			->count();
	}

	public function findTickerByTicker(string $ticker): ?Ticker
	{
		return $this->findOne([
			'ticker' => $ticker,
		]);
	}
}
