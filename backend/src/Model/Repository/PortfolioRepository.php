<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Portfolio;

/** @extends ARepository<Portfolio> */
final class PortfolioRepository extends ARepository
{
	/** @return list<Portfolio> */
	public function findPortfolios(int $userId): array
	{
		return $this->findAll([
			'user_id' => $userId,
		]);
	}

	public function findPortfolio(int $userId, int $portfolioId): ?Portfolio
	{
		return $this->findOne([
			'user_id' => $userId,
			'id' => $portfolioId,
		]);
	}

	public function findDefaultPortfolio(int $userId): Portfolio
	{
		$defaultPortfolio = $this->findOne([
			'user_id' => $userId,
			'is_default' => true,
		]);
		assert($defaultPortfolio instanceof Portfolio);
		return $defaultPortfolio;
	}
}
