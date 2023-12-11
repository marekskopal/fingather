<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Transaction;
use Safe\DateTime;

/** @extends ARepository<Asset> */
class AssetRepository extends ARepository
{
	/** @return iterable<Asset> */
	public function findOpenAssets(int $userId, DateTime $dateTime): iterable
	{
		$transactionRepository = $this->orm->getRepository(Transaction::class);
		assert($transactionRepository instanceof TransactionRepository);

		$transactionIds = [];
		foreach ($transactionRepository->findOpenTransactions($userId, $dateTime) as $transaction) {
			$transactionIds[] = $transaction->getId();
		}

		return $this->findAll([
			'user.id' => $userId,
			'transaction.id' => $transactionIds,
		]);
	}

	/** @return iterable<Asset> */
	public function findOpenAssetsByGroup(int $userId, int $groupId): iterable
	{
		return $this->findAll([
			'user.id' => $userId,
		]);
	}

	public function findAsset(int $assetId, int $userId): ?Asset
	{
		return $this->findOne([
			'id' => $assetId,
			'user.id' => $userId,
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
