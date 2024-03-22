<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Safe\DateTimeImmutable;

class DataProvider
{
	public function __construct(
		private readonly AssetDataProvider $assetDataProvider,
		private readonly GroupDataProvider $groupDataProvider,
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly BenchmarkDataProvider $benchmarkDataProvider,
		private readonly TransactionProvider $transactionProvider,
	) {
	}

	public function deleteUserData(
		User $user,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $date = null,
		bool $recalculateTransactions = false,
	): void
	{
		$this->assetDataProvider->deleteAssetData($user, $portfolio);
		$this->groupDataProvider->deleteUserGroupData($user, $portfolio, $date);
		$this->portfolioDataProvider->deletePortfolioData($user, $portfolio, $date);
		$this->benchmarkDataProvider->deleteBenchmarkData($user, $portfolio, $date);

		if (!$recalculateTransactions) {
			return;
		}

		foreach ($this->transactionProvider->getTransactions($user, $portfolio) as $transaction) {
			$this->transactionProvider->updateTransactionDefaultCurrency($transaction);
		}
	}
}
