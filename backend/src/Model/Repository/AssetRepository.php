<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Asset;
use Safe\DateTimeImmutable;

/** @extends ARepository<Asset> */
class AssetRepository extends ARepository
{
	/** @return array<int, Asset> */
	public function findOpenAssets(int $userId, DateTimeImmutable $dateTime): array
	{
		$openAssetSelect = $this->orm->getSource(Asset::class)
			->getDatabase()
			->select('asset_id')
			->from('transactions')
			->where('user_id', $userId)
			->where('created', '<=', $dateTime)
			->groupBy('asset_id')
			->having('SUM(units)', '>', 0);

		return $this->select()
			->where('user_id', $userId)
			->where('id', 'in', $openAssetSelect)
			->orderBy('ticker.name')
			->fetchAll();
	}

	/** @return array<int, Asset> */
	public function findOpenAssetsByGroup(int $userId, int $groupId, DateTimeImmutable $dateTime): array
	{
		$openAssetSelect = $this->orm->getSource(Asset::class)
			->getDatabase()
			->select('asset_id')
			->from('transactions')
			->where('user_id', $userId)
			->where('created', '<=', $dateTime)
			->groupBy('asset_id')
			->having('SUM(units)', '>', 0);

		return $this->select()
			->where('user_id', $userId)
			->where('group_id', $groupId)
			->where('id', 'in', $openAssetSelect)
			->fetchAll();
	}

	public function findAsset(int $assetId, int $userId): ?Asset
	{
		return $this->findOne([
			'id' => $assetId,
			'user_id' => $userId,
		]);
	}

	public function findAssetByTickerId(int $userId, int $tickerId): ?Asset
	{
		return $this->findOne([
			'user_id' => $userId,
			'ticker_id' => $tickerId,
		]);
	}
}
