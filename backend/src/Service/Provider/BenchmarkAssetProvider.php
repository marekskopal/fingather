<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\BenchmarkAsset;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\BenchmarkAssetRepository;

final readonly class BenchmarkAssetProvider implements BenchmarkAssetProviderInterface
{
	public function __construct(private BenchmarkAssetRepository $benchmarkAssetRepository)
	{
	}

	/** @return list<BenchmarkAsset> */
	public function getBenchmarkAssets(): array
	{
		return $this->benchmarkAssetRepository->findBenchmarkAssets();
	}

	public function getBenchmarkAsset(int $id): ?BenchmarkAsset
	{
		return $this->benchmarkAssetRepository->findBenchmarkAssetById($id);
	}

	public function getBenchmarkAssetByTickerId(int $tickerId): ?BenchmarkAsset
	{
		return $this->benchmarkAssetRepository->findBenchmarkAssetByTickerId($tickerId);
	}

	public function createBenchmarkAsset(Ticker $ticker): BenchmarkAsset
	{
		$benchmarkAsset = new BenchmarkAsset(ticker: $ticker);
		$this->benchmarkAssetRepository->persist($benchmarkAsset);

		return $benchmarkAsset;
	}

	public function deleteBenchmarkAsset(BenchmarkAsset $benchmarkAsset): void
	{
		$this->benchmarkAssetRepository->delete($benchmarkAsset);
	}
}
