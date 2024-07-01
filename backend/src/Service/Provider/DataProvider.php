<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Psr\Log\LoggerInterface;

class DataProvider
{
	public function __construct(
		private readonly AssetDataProvider $assetDataProvider,
		private readonly GroupDataProvider $groupDataProvider,
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly BenchmarkDataProvider $benchmarkDataProvider,
		private readonly CountryDataProvider $countryDataProvider,
		private readonly SectorDataProvider $sectorDataProvider,
		private readonly IndustryDataProvider $industryDataProvider,
		private readonly TransactionProvider $transactionProvider,
		private readonly LoggerInterface $logger,
	) {
	}

	public function deleteData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null,): void
	{
		$this->logger->log('delete', 'Deleting data'
			. ($user !== null ? ' for user ' . $user->getId() : '')
			. ($portfolio !== null ? ' for portfolio ' . $portfolio->getId() : '')
			. ($date !== null ? ' for date ' . $date->format('Y-m-d') : ''));

		$this->assetDataProvider->deleteAssetData($user, $portfolio, $date);
		$this->groupDataProvider->deleteUserGroupData($user, $portfolio, $date);
		$this->portfolioDataProvider->deletePortfolioData($user, $portfolio, $date);
		$this->benchmarkDataProvider->deleteBenchmarkData($user, $portfolio, $date);
		$this->countryDataProvider->deleteUserCountryData($user, $portfolio, $date);
		$this->sectorDataProvider->deleteUserSectorData($user, $portfolio, $date);
		$this->industryDataProvider->deleteUserIndustryData($user, $portfolio, $date);
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
