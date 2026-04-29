<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use ArrayIterator;
use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\BenchmarkDataProviderInterface;
use FinGather\Service\Provider\CountryDataProviderInterface;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\GroupDataProviderInterface;
use FinGather\Service\Provider\IndustryDataProviderInterface;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\SectorDataProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(DataProvider::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(Transaction::class)]
#[UsesClass(User::class)]
final class DataProviderTest extends TestCase
{
	public function testDeleteDataWithoutFirstDateInvokesEachProviderOnce(): void
	{
		$counts = $this->makeCounters();
		$provider = $this->makeProvider($counts, []);

		$provider->deleteData(UserFixture::getUser(), PortfolioFixture::getPortfolio());

		self::assertSame(1, $counts['asset']);
		self::assertSame(1, $counts['group']);
		self::assertSame(1, $counts['portfolio']);
		self::assertSame(1, $counts['benchmark']);
		self::assertSame(1, $counts['country']);
		self::assertSame(1, $counts['sector']);
		self::assertSame(1, $counts['industry']);
	}

	public function testDeleteDataWithFirstDateIteratesPerDayUpToToday(): void
	{
		$counts = $this->makeCounters();
		$provider = $this->makeProvider($counts, []);

		$today = new DateTimeImmutable('today');
		$firstDate = $today->modify('-3 days');

		$provider->deleteData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), $firstDate);

		// firstDate inclusive through today inclusive → 4 days
		self::assertSame(4, $counts['asset']);
	}

	public function testDeleteUserDataDoesNotRecalculateTransactionsByDefault(): void
	{
		$counts = $this->makeCounters();
		$transactions = [TransactionFixture::getTransaction()];
		$provider = $this->makeProvider($counts, $transactions);

		$provider->deleteUserData(UserFixture::getUser(), PortfolioFixture::getPortfolio());

		self::assertSame(0, $counts['updateTransaction']);
	}

	public function testDeleteUserDataRecalculatesTransactionsWhenRequested(): void
	{
		$counts = $this->makeCounters();
		$transactions = [TransactionFixture::getTransaction(id: 1), TransactionFixture::getTransaction(id: 2)];
		$provider = $this->makeProvider($counts, $transactions);

		$provider->deleteUserData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), recalculateTransactions: true);

		self::assertSame(2, $counts['updateTransaction']);
	}

	/** @return array<string, int> */
	private function makeCounters(): array
	{
		return [
			'asset' => 0,
			'group' => 0,
			'portfolio' => 0,
			'benchmark' => 0,
			'country' => 0,
			'sector' => 0,
			'industry' => 0,
			'updateTransaction' => 0,
		];
	}

	/**
	 * @param array<string, int> &$counts
	 * @param list<Transaction>  $transactions
	 */
	private function makeProvider(array &$counts, array $transactions): DataProvider
	{
		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('deleteAssetData')->willReturnCallback(static function () use (&$counts): void {
			$counts['asset']++;
		});

		$groupDataProvider = self::createStub(GroupDataProviderInterface::class);
		$groupDataProvider->method('deleteUserGroupData')->willReturnCallback(static function () use (&$counts): void {
			$counts['group']++;
		});

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('deletePortfolioData')->willReturnCallback(static function () use (&$counts): void {
			$counts['portfolio']++;
		});

		$benchmarkDataProvider = self::createStub(BenchmarkDataProviderInterface::class);
		$benchmarkDataProvider->method('deleteBenchmarkData')->willReturnCallback(static function () use (&$counts): void {
			$counts['benchmark']++;
		});

		$countryDataProvider = self::createStub(CountryDataProviderInterface::class);
		$countryDataProvider->method('deleteUserCountryData')->willReturnCallback(static function () use (&$counts): void {
			$counts['country']++;
		});

		$sectorDataProvider = self::createStub(SectorDataProviderInterface::class);
		$sectorDataProvider->method('deleteUserSectorData')->willReturnCallback(static function () use (&$counts): void {
			$counts['sector']++;
		});

		$industryDataProvider = self::createStub(IndustryDataProviderInterface::class);
		$industryDataProvider->method('deleteUserIndustryData')->willReturnCallback(static function () use (&$counts): void {
			$counts['industry']++;
		});

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getTransactions')->willReturn(new ArrayIterator($transactions));
		$transactionProvider->method('updateTransactionDefaultCurrency')->willReturnCallback(
			static function (Transaction $tx) use (&$counts): Transaction {
				$counts['updateTransaction']++;
				return $tx;
			},
		);

		return new DataProvider(
			assetDataProvider: $assetDataProvider,
			groupDataProvider: $groupDataProvider,
			portfolioDataProvider: $portfolioDataProvider,
			benchmarkDataProvider: $benchmarkDataProvider,
			countryDataProvider: $countryDataProvider,
			sectorDataProvider: $sectorDataProvider,
			industryDataProvider: $industryDataProvider,
			transactionProvider: $transactionProvider,
			logger: self::createStub(LoggerInterface::class),
		);
	}
}
