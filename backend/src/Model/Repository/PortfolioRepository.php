<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

/** @extends ARepository<Portfolio> */
class PortfolioRepository extends ARepository
{
	/** @return iterable<Portfolio> */
	public function findPortfolios(): iterable
	{
		return $this->findAll();
	}

	public function findPortfolio(int $portfolioId): ?User
	{
		return $this->findOne([
			'id' => $portfolioId,
		]);
	}
}
