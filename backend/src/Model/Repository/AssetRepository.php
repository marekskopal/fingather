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
	public function findAssets(int $userId, ?int $portfolioId = null, ?DateTimeImmutable $dateTime = null, ?int $groupId = null): array
	{
		return $this->getAssetsSelect($userId, $portfolioId, $dateTime, $groupId)->fetchAll();
	}

	public function countAssets(int $userId, ?int $portfolioId = null, ?DateTimeImmutable $dateTime = null, ?int $groupId = null): int
	{
		return $this->getAssetsSelect($userId, $portfolioId, $dateTime, $groupId)->count();
	}

	/** @return Select<Asset> */
	private function getAssetsSelect(
		int $userId,
		?int $portfolioId = null,
		?DateTimeImmutable $dateTime = null,
		?int $groupId = null,
	): Select
	{
		$assetsSelect = $this->select()
			->where('user_id', $userId);

		if ($portfolioId !== null) {
			$assetsSelect->where('portfolio_id', $portfolioId);
		}

		if ($dateTime !== null) {
			$transactionAssetSelect = $this->orm->getSource(Asset::class)
				->getDatabase()
				->select('asset_id')
				->from('transactions')
				->where('user_id', $userId)
				->where('portfolio_id', $portfolioId)
				->where('action_created', '<=', $dateTime)
				->where('action_type', 'in', [TransactionActionTypeEnum::Buy->value, TransactionActionTypeEnum::Sell->value])
				->groupBy('asset_id');

			$assetsSelect->where('id', 'in', $transactionAssetSelect);
		}

		if ($groupId !== null) {
			$assetsSelect->where('group_id', $groupId);
		}

		$assetsSelect->orderBy('ticker.name');

		return $assetsSelect;
	}

	public function findAsset(int $assetId, int $userId): ?Asset
	{
		return $this->findOne([
			'id' => $assetId,
			'user_id' => $userId,
		]);
	}

	public function findAssetByTickerId(int $userId, int $portfolioId, int $tickerId): ?Asset
	{
		return $this->findOne([
			'user_id' => $userId,
			'portfolio_id' => $portfolioId,
			'ticker_id' => $tickerId,
		]);
	}
}
