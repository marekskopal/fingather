<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\BenchmarkData;
use FinGather\Model\Entity\PortfolioData;

/** @extends ARepository<BenchmarkData> */
class BenchmarkDataRepository extends ARepository
{
	public function findBenchmarkData(int $userId, int $assetId, DateTimeImmutable $date): ?BenchmarkData
	{
		return $this->findOne([
			'user_id' => $userId,
			'asset_id' => $assetId,
			'date' => $date,
		]);
	}

	public function deleteBenchmarkData(int $userId, DateTimeImmutable $date): void
	{
		$this->orm->getSource(PortfolioData::class)
			->getDatabase()
			->delete('benchmark_datas')
			->where('user_id', $userId)
			->where('date', '>=', $date)
			->run();
	}
}
