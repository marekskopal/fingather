<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\PortfolioData;

/** @extends ARepository<PortfolioData> */
class PortfolioDataRepository extends ARepository
{
	public function findPortfolioData(int $userId, DateTimeImmutable $date): ?PortfolioData
	{
		return $this->findOne([
			'user_id' => $userId,
			'date' => $date,
		]);
	}

	public function deletePortfolioData(int $userId, DateTimeImmutable $date): void
	{
		$this->orm->getSource(PortfolioData::class)
			->getDatabase()
			->delete('portfolio_datas')
			->where('user_id', $userId)
			->where('date', '>=', $date)
			->run();
	}
}
