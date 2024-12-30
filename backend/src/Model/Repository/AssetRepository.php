<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Transaction;
use Iterator;
use MarekSkopal\ORM\Query\Select;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Asset> */
final class AssetRepository extends AbstractRepository
{
	/** @return Iterator<Asset> */
	public function findAssets(
		int $userId,
		?int $portfolioId = null,
		?DateTimeImmutable $dateTime = null,
		?int $groupId = null,
		?int $countryId = null,
		?int $sectorId = null,
		?int $industryId = null,
	): Iterator
	{
		return $this->getAssetsSelect($userId, $portfolioId, $dateTime, $groupId, $countryId, $sectorId, $industryId)->fetchAll();
	}

	public function countAssets(
		int $userId,
		?int $portfolioId = null,
		?DateTimeImmutable $dateTime = null,
		?int $groupId = null,
		?int $countryId = null,
		?int $sectorId = null,
		?int $industryId = null,
	): int
	{
		return $this->getAssetsSelect($userId, $portfolioId, $dateTime, $groupId, $countryId, $sectorId, $industryId)->count();
	}

	/** @return Select<Asset> */
	private function getAssetsSelect(
		int $userId,
		?int $portfolioId = null,
		?DateTimeImmutable $dateTime = null,
		?int $groupId = null,
		?int $countryId = null,
		?int $sectorId = null,
		?int $industryId = null,
	): Select
	{
		$assetsSelect = $this->select()
			->where(['user_id' => $userId]);

		if ($portfolioId !== null) {
			$assetsSelect->where(['portfolio_id' => $portfolioId]);
		}

		if ($dateTime !== null) {
			$transactionAssetSelect = $this->queryProvider
				->select(Transaction::class)
				->columns(['asset_id'])
				->where(['user_id' => $userId]);
			if ($portfolioId !== null) {
				$transactionAssetSelect->where(['portfolio_id' => $portfolioId]);
			}
			$transactionAssetSelect
				->where(['action_created', '<=', $dateTime])
				->where(['action_type', 'in', [TransactionActionTypeEnum::Buy->value, TransactionActionTypeEnum::Sell->value]])
				->groupBy(['asset_id']);

			$assetsSelect->where(['id', 'in', $transactionAssetSelect]);
		}

		if ($groupId !== null) {
			$assetsSelect->where(['group_id' => $groupId]);
		}

		if ($countryId !== null) {
			$assetsSelect->where(['ticker.country_id' => $countryId]);
		}

		if ($sectorId !== null) {
			$assetsSelect->where(['ticker.sector_id' => $sectorId]);
		}

		if ($industryId !== null) {
			$assetsSelect->where(['ticker.industry_id' => $industryId]);
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

	public function findAssetByTickerId(int $tickerId, ?int $userId = null, ?int $portfolioId = null): ?Asset
	{
		$assetsSelect = $this->select()
			->where(['ticker_id' => $tickerId]);

		if ($userId !== null) {
			$assetsSelect->where(['user_id' => $userId]);
		}

		if ($portfolioId !== null) {
			$assetsSelect->where(['portfolio_id' => $portfolioId]);
		}

		return $assetsSelect->fetchOne();
	}
}
