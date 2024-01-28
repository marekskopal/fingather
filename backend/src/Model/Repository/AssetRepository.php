<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use Safe\DateTimeImmutable;

/** @extends ARepository<Asset> */
class AssetRepository extends ARepository
{
	/** @return array<int, Asset> */
	public function findAssets(int $userId, ?int $portfolioId = null): array
	{
		return $this->getAssetsSelect($userId, $portfolioId)->fetchAll();
	}

	public function countAssets(int $userId, ?int $portfolioId = null): int
	{
		return $this->getAssetsSelect($userId, $portfolioId)->count();
	}

	/** @return Select<Asset> */
	private function getAssetsSelect(int $userId, ?int $portfolioId = null): Select
	{
		$assetsSelect = $this->select()
			->where('user_id', $userId);

		if ($portfolioId !== null) {
			$assetsSelect->where('portfolio_id', $portfolioId);
		}

		$assetsSelect->orderBy('ticker.name');

		return $assetsSelect;
	}

	/** @return array<int, Asset> */
	public function findOpenAssets(int $userId, DateTimeImmutable $dateTime): array
	{
		$openAssetSelect = $this->orm->getSource(Asset::class)
			->getDatabase()
			->select('asset_id')
			->from('transactions')
			->where('user_id', $userId)
			->where('action_created', '<=', $dateTime)
			->where('action_type', 'in', [TransactionActionTypeEnum::Buy->value, TransactionActionTypeEnum::Sell->value])
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
			->where('action_created', '<=', $dateTime)
			->where('action_type', 'in', [TransactionActionTypeEnum::Buy->value, TransactionActionTypeEnum::Sell->value])
			->groupBy('asset_id')
			->having('SUM(units)', '>', 0);

		return $this->select()
			->where('user_id', $userId)
			->where('group_id', $groupId)
			->where('id', 'in', $openAssetSelect)
			->fetchAll();
	}

	/** @return array<int, Asset> */
	public function findClosedAssets(int $userId, DateTimeImmutable $dateTime): array
	{
		$closedAssetSelect = $this->orm->getSource(Asset::class)
			->getDatabase()
			->select('asset_id')
			->from('transactions')
			->where('user_id', $userId)
			->where('action_created', '<=', $dateTime)
			->where('action_type', 'in', [TransactionActionTypeEnum::Buy->value, TransactionActionTypeEnum::Sell->value])
			->groupBy('asset_id')
			->having('SUM(units)', '<=', 0);

		return $this->select()
			->where('user_id', $userId)
			->where('id', 'in', $closedAssetSelect)
			->orderBy('ticker.name')
			->fetchAll();
	}

	/** @return array<int, Asset> */
	public function findWatchedAssets(int $userId): array
	{
		$watchedAssetSelect = $this->orm->getSource(Asset::class)
			->getDatabase()
			->select('asset_id')
			->from('transactions')
			->where('user_id', $userId)
			->where('action_type', 'in', [TransactionActionTypeEnum::Buy->value, TransactionActionTypeEnum::Sell->value])
			->groupBy('asset_id');

		return $this->select()
			->where('user_id', $userId)
			->where('id', 'not in', $watchedAssetSelect)
			->orderBy('ticker.name')
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
