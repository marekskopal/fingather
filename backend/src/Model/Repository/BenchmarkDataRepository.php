<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\BenchmarkData;
use FinGather\Model\Entity\PortfolioData;

/** @extends ARepository<BenchmarkData> */
class BenchmarkDataRepository extends ARepository
{
	public function findBenchmarkData(int $userId, int $assetId, DateTimeImmutable $date, DateTimeImmutable $fromDate): ?BenchmarkData
	{
		return $this->findOne([
			'user_id' => $userId,
			'asset_id' => $assetId,
			'date' => $date,
			'from_date' => $fromDate,
		]);
	}

	public function deleteBenchmarkData(int $userId, int $portfolioId, DateTimeImmutable $date): void
	{
		$this->orm->getSource(PortfolioData::class)
			->getDatabase()
			->delete('benchmark_datas')
			->where('user_id', $userId)
			->where('portfolio_id', $portfolioId)
			->where('date', '>=', $date)
			->run();
	}
}
