<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\BenchmarkAsset;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<BenchmarkAsset> */
final class BenchmarkAssetRepository extends AbstractRepository
{
	/** @return Iterator<BenchmarkAsset> */
	public function findBenchmarkAssets(): Iterator
	{
		return $this->findAll();
	}

	public function findBenchmarkAssetById(int $id): ?BenchmarkAsset
	{
		return $this->findOne(['id' => $id]);
	}

	public function findBenchmarkAssetByTickerId(int $tickerId): ?BenchmarkAsset
	{
		return $this->findOne(['ticker_id' => $tickerId]);
	}
}
