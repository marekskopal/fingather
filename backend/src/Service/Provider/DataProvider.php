<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Psr\Log\LoggerInterface;

final readonly class DataProvider
{
	public function __construct(
		private AssetDataProviderInterface $assetDataProvider,
		private GroupDataProvider $groupDataProvider,
		private PortfolioDataProvider $portfolioDataProvider,
		private BenchmarkDataProvider $benchmarkDataProvider,
		private CountryDataProvider $countryDataProvider,
		private SectorDataProvider $sectorDataProvider,
		private IndustryDataProvider $industryDataProvider,
		private TransactionProviderInterface $transactionProvider,
		private LoggerInterface $logger,
	) {
	}

	public function deleteData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $firstDate = null,): void
	{
		if ($firstDate === null) {
			$this->processDeleteData(user: $user, portfolio: $portfolio);

			return;
		}

		$today = new DateTimeImmutable('today');
		$interval = new DateInterval('P1D');
		$period = new DatePeriod($firstDate, $interval, $today->modify('+1 day'));

		foreach ($period as $date) {
			$this->processDeleteData(user: $user, portfolio: $portfolio, date: $date);
		}
	}

	public function deleteUserData(
		User $user,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $firstDate = null,
		bool $recalculateTransactions = false,
	): void
	{
		$this->deleteData(user: $user, portfolio: $portfolio, firstDate: $firstDate);

		if (!$recalculateTransactions) {
			return;
		}

		foreach ($this->transactionProvider->getTransactions($user, $portfolio) as $transaction) {
			$this->transactionProvider->updateTransactionDefaultCurrency($transaction);
		}
	}

	private function processDeleteData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->logger->info('Deleting data'
			. ($user !== null ? ' for user ' . $user->id : '')
			. ($portfolio !== null ? ' for portfolio ' . $portfolio->id : '')
			. ($date !== null ? ' for date ' . $date->format('Y-m-d') : ''));

		$this->assetDataProvider->deleteAssetData($user, $portfolio, $date);
		$this->groupDataProvider->deleteUserGroupData($user, $portfolio, $date);
		$this->portfolioDataProvider->deletePortfolioData($user, $portfolio, $date);
		$this->benchmarkDataProvider->deleteBenchmarkData($user, $portfolio);
		$this->countryDataProvider->deleteUserCountryData($user, $portfolio, $date);
		$this->sectorDataProvider->deleteUserSectorData($user, $portfolio, $date);
		$this->industryDataProvider->deleteUserIndustryData($user, $portfolio, $date);
	}
}
