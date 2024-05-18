<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

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

	public function deleteData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null,): void
	{
		$this->assetDataProvider->deleteAssetData($user, $portfolio, $date);
		$this->groupDataProvider->deleteUserGroupData($user, $portfolio, $date);
		$this->portfolioDataProvider->deletePortfolioData($user, $portfolio, $date);
		$this->benchmarkDataProvider->deleteBenchmarkData($user, $portfolio, $date);
	}

	public function deleteUserData(
		User $user,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $date = null,
		bool $recalculateTransactions = false,
	): void
	{
		$this->deleteData(user: $user, portfolio: $portfolio, date: $date);

		if (!$recalculateTransactions) {
			return;
		}

		foreach ($this->transactionProvider->getTransactions($user, $portfolio) as $transaction) {
			$this->transactionProvider->updateTransactionDefaultCurrency($transaction);
		}
	}
}
