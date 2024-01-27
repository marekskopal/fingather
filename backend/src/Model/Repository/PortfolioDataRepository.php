<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\PortfolioData;

/** @extends ARepository<PortfolioData> */
class PortfolioDataRepository extends ARepository
{
	public function findPortfolioData(int $userId, int $portfolioId, DateTimeImmutable $date): ?PortfolioData
	{
		return $this->findOne([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'date' => $date,
		]);
	}

	public function deletePortfolioData(int $userId, int $portfolioId, DateTimeImmutable $date): void
	{
		$this->orm->getSource(PortfolioData::class)
			->getDatabase()
			->delete('portfolio_datas')
			->where('user_id', $userId)
			->where('portfolio_id', $portfolioId)
			->where('date', '>=', $date)
			->run();
	}
}
