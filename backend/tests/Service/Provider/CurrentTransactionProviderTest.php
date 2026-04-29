<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use ArrayIterator;
use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\Provider\CurrentTransactionProvider;
use FinGather\Service\Provider\TransactionCutoffFinder;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CurrentTransactionProvider::class)]
#[UsesClass(TransactionCutoffFinder::class)]
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
final class CurrentTransactionProviderTest extends TestCase
{
	private User $user;

	private Portfolio $portfolio;

	private Asset $asset1;

	private Asset $asset2;

	protected function setUp(): void
	{
		$this->user = UserFixture::getUser();
		$this->portfolio = PortfolioFixture::getPortfolio();
		$this->asset1 = AssetFixture::getAsset(id: 1);
		$this->asset2 = AssetFixture::getAsset(id: 2);
	}

	public function testReturnsAllTransactionsWhenNoFilters(): void
	{
		$transactions = [
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Buy, date: '2024-01-01'),
			$this->makeTransaction(asset: $this->asset2, actionType: TransactionActionTypeEnum::Buy, date: '2024-02-01'),
		];

		$provider = $this->makeProvider($transactions);

		$result = $provider->getTransactions(user: $this->user, portfolio: $this->portfolio);

		self::assertCount(2, $result);
	}

	public function testFiltersByAsset(): void
	{
		$transactions = [
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Buy, date: '2024-01-01'),
			$this->makeTransaction(asset: $this->asset2, actionType: TransactionActionTypeEnum::Buy, date: '2024-02-01'),
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Sell, date: '2024-03-01'),
		];

		$provider = $this->makeProvider($transactions);

		$result = $provider->getTransactions(user: $this->user, portfolio: $this->portfolio, asset: $this->asset1);

		self::assertCount(2, $result);
		foreach ($result as $tx) {
			self::assertSame(1, $tx->asset->id);
		}
	}

	public function testFiltersByActionType(): void
	{
		$transactions = [
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Buy, date: '2024-01-01'),
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Dividend, date: '2024-02-01'),
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Sell, date: '2024-03-01'),
		];

		$provider = $this->makeProvider($transactions);

		$result = $provider->getTransactions(
			user: $this->user,
			portfolio: $this->portfolio,
			actionTypes: [TransactionActionTypeEnum::Buy, TransactionActionTypeEnum::Sell],
		);

		self::assertCount(2, $result);
		self::assertNotContains(TransactionActionTypeEnum::Dividend, array_map(static fn (Transaction $tx) => $tx->actionType, $result));
	}

	public function testActionCreatedBeforeCutsOffLaterTransactions(): void
	{
		$transactions = [
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Buy, date: '2024-01-01'),
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Buy, date: '2024-02-01'),
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Buy, date: '2024-03-01'),
		];

		$provider = $this->makeProvider($transactions);

		$result = $provider->getTransactions(
			user: $this->user,
			portfolio: $this->portfolio,
			actionCreatedBefore: new DateTimeImmutable('2024-02-15'),
		);

		// First two transactions are on or before 2024-02-15
		self::assertCount(2, $result);
	}

	public function testLoadTransactionsIsCachedAcrossCalls(): void
	{
		$transactions = [
			$this->makeTransaction(asset: $this->asset1, actionType: TransactionActionTypeEnum::Buy, date: '2024-01-01'),
		];

		$callCount = 0;
		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getTransactions')->willReturnCallback(
			function () use (&$callCount, $transactions): ArrayIterator {
				$callCount++;
				return new ArrayIterator($transactions);
			},
		);

		$provider = new CurrentTransactionProvider($transactionProvider, new TransactionCutoffFinder());

		$provider->loadTransactions($this->user, $this->portfolio);
		$provider->loadTransactions($this->user, $this->portfolio);

		self::assertSame(1, $callCount);
	}

	public function testClearForcesReloadOnNextLoadTransactions(): void
	{
		$callCount = 0;
		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getTransactions')->willReturnCallback(
			function () use (&$callCount): ArrayIterator {
				$callCount++;
				return new ArrayIterator([]);
			},
		);

		$provider = new CurrentTransactionProvider($transactionProvider, new TransactionCutoffFinder());

		$provider->loadTransactions($this->user, $this->portfolio);
		$provider->clear();
		$provider->loadTransactions($this->user, $this->portfolio);

		self::assertSame(2, $callCount);
	}

	private function makeTransaction(Asset $asset, TransactionActionTypeEnum $actionType, string $date): Transaction
	{
		return TransactionFixture::getTransaction(
			asset: $asset,
			actionType: $actionType,
			actionCreated: new DateTimeImmutable($date),
		);
	}

	/** @param list<Transaction> $transactions */
	private function makeProvider(array $transactions): CurrentTransactionProvider
	{
		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getTransactions')->willReturn(new ArrayIterator($transactions));

		return new CurrentTransactionProvider($transactionProvider, new TransactionCutoffFinder());
	}
}
