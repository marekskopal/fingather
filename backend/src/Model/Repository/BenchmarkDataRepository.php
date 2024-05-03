<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\BenchmarkData;
use FinGather\Model\Entity\PortfolioData;

/** @extends ARepository<BenchmarkData> */
final class BenchmarkDataRepository extends ARepository
{
	public function findBenchmarkData(int $portfolioId, int $assetId, DateTimeImmutable $date, DateTimeImmutable $fromDate): ?BenchmarkData
	{
		return $this->findOne([
			'portfolio_id' => $portfolioId,
			'asset_id' => $assetId,
			'date' => $date,
			'from_date' => $fromDate,
		]);
	}

	public function deleteBenchmarkData(int $userId, ?int $portfolioId = null, ?DateTimeImmutable $date = null): void
	{
		$deleteBenchmarkData = $this->orm->getSource(PortfolioData::class)
			->getDatabase()
			->delete('benchmark_datas')
			->where('user_id', $userId);

		if ($portfolioId !== null) {
			$deleteBenchmarkData->where('portfolio_id', $portfolioId);
		}

		if ($date !== null) {
			$deleteBenchmarkData->where('date', '>=', $date);
		}

		$deleteBenchmarkData->run();
	}
}
