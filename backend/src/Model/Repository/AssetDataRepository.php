<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\AssetData;
use FinGather\Model\Entity\PortfolioData;

/** @extends ABulkInsertRepository<AssetData> */
final class AssetDataRepository extends ABulkInsertRepository
{
	public function findAssetData(int $userId, int $portfolioId, int $assetId, DateTimeImmutable $date): ?AssetData
	{
		return $this->findOne([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'asset_id' => $assetId,
			'date' => $date,
		]);
	}

	public function deleteAssetData(?int $userId = null, ?int $portfolioId = null, ?DateTimeImmutable $date = null): void
	{
		$deleteAssetData = $this->orm->getSource(PortfolioData::class)
			->getDatabase()
			->delete('asset_datas');

		if ($userId !== null) {
			$deleteAssetData->where('user_id', $userId);
		}

		if ($portfolioId !== null) {
			$deleteAssetData->where('portfolio_id', $portfolioId);
		}

		if ($date !== null) {
			$deleteAssetData->where('date', '>=', $date);
		}

		$deleteAssetData->run();
	}
}
