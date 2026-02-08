<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Strategy;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Strategy> */
final class StrategyRepository extends AbstractRepository
{
	/** @return Iterator<Strategy> */
	public function findStrategies(int $userId, int $portfolioId): Iterator
	{
		return $this->findAll([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
		]);
	}

	public function findStrategy(int $userId, int $strategyId): ?Strategy
	{
		return $this->findOne([
			'user_id' => $userId,
			'id' => $strategyId,
		]);
	}

	public function findDefaultStrategy(int $userId, int $portfolioId): ?Strategy
	{
		return $this->findOne([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'is_default' => true,
		]);
	}
}
