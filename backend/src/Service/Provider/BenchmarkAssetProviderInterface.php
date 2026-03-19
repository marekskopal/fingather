<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\BenchmarkAsset;
use FinGather\Model\Entity\Ticker;
use Iterator;

interface BenchmarkAssetProviderInterface
{
	/** @return Iterator<BenchmarkAsset> */
	public function getBenchmarkAssets(): Iterator;

	public function getBenchmarkAsset(int $id): ?BenchmarkAsset;

	public function getBenchmarkAssetByTickerId(int $tickerId): ?BenchmarkAsset;

	public function createBenchmarkAsset(Ticker $ticker): BenchmarkAsset;

	public function deleteBenchmarkAsset(BenchmarkAsset $benchmarkAsset): void;
}
