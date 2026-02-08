<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\StrategyItem;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<StrategyItem> */
final class StrategyItemRepository extends AbstractRepository
{
	/** @return Iterator<StrategyItem> */
	public function findStrategyItems(int $strategyId): Iterator
	{
		return $this->findAll([
			'strategy_id' => $strategyId,
		]);
	}

	public function deleteStrategyItems(int $strategyId): void
	{
		foreach ($this->findStrategyItems($strategyId) as $strategyItem) {
			$this->delete($strategyItem);
		}
	}
}
