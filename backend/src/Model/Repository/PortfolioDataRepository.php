<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\PortfolioData;

/** @extends ARepository<PortfolioData> */
final class PortfolioDataRepository extends ARepository
{
	public function findPortfolioData(int $userId, int $portfolioId, DateTimeImmutable $date): ?PortfolioData
	{
		return $this->findOne([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'date' => $date,
		]);
	}

	public function deletePortfolioData(int $userId, ?int $portfolioId = null, ?DateTimeImmutable $date = null): void
	{
		$deletePortfolioData = $this->orm->getSource(PortfolioData::class)
			->getDatabase()
			->delete('portfolio_datas')
			->where('user_id', $userId);

		if ($portfolioId !== null) {
			$deletePortfolioData->where('portfolio_id', $portfolioId);
		}

		if ($date !== null) {
			$deletePortfolioData->where('date', '>=', $date);
		}

		$deletePortfolioData->run();
	}
}
