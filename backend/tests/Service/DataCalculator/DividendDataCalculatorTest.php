<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Service\DataCalculator\DividendDataCalculator;
use FinGather\Service\DataCalculator\Dto\DividendDataAssetDto;
use FinGather\Service\DataCalculator\Dto\DividendDataIntervalDto;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
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
use FinGather\Utils\DateTimeUtils;
use FinGather\Model\Entity\Asset;

#[CoversClass(DividendDataCalculator::class)]
#[UsesClass(DividendDataIntervalDto::class)]
#[UsesClass(DividendDataAssetDto::class)]
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
#[UsesClass(DateTimeUtils::class)]
#[UsesClass(Asset::class)]
final class DividendDataCalculatorTest extends TestCase
{
	public function testReturnsEmptyWhenNoFirstTransaction(): void
	{
		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn(null);

		$calculator = new DividendDataCalculator($transactionProvider);

		$result = $calculator->getDividendData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), RangeEnum::All);

		self::assertSame([], $result);
	}

	public function testReturnsEmptyWhenNoDividendTransactions(): void
	{
		$firstTransaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable('2024-01-01'),
		);

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn($firstTransaction);
		$transactionProvider->method('getTransactions')->willReturn(new ArrayIterator([]));

		$calculator = new DividendDataCalculator($transactionProvider);

		$result = $calculator->getDividendData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), RangeEnum::All);

		self::assertSame([], $result);
	}

	public function testGroupsByDayForSevenDaysRange(): void
	{
		$firstTransaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable('2024-03-01'),
		);

		$ticker = TickerFixture::getTicker(id: 1);
		$asset = AssetFixture::getAsset(id: 1, ticker: $ticker);

		$tx1 = TransactionFixture::getTransaction(
			id: 1,
			asset: $asset,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-03-01'),
			priceDefaultCurrency: new Decimal('50'),
		);
		$tx2 = TransactionFixture::getTransaction(
			id: 2,
			asset: $asset,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-03-02'),
			priceDefaultCurrency: new Decimal('30'),
		);

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn($firstTransaction);
		$transactionProvider->method('getTransactions')->willReturn(new ArrayIterator([$tx1, $tx2]));

		$calculator = new DividendDataCalculator($transactionProvider);

		$result = $calculator->getDividendData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), RangeEnum::SevenDays);

		self::assertCount(2, $result);
		self::assertSame('2024-03-01', $result[0]->interval);
		self::assertSame('2024-03-02', $result[1]->interval);
	}

	public function testGroupsByMonthForThreeMonthsRange(): void
	{
		$firstTransaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable('2024-01-01'),
		);

		$ticker = TickerFixture::getTicker(id: 1);
		$asset = AssetFixture::getAsset(id: 1, ticker: $ticker);

		$tx1 = TransactionFixture::getTransaction(
			id: 1,
			asset: $asset,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-01-10'),
			priceDefaultCurrency: new Decimal('50'),
		);
		$tx2 = TransactionFixture::getTransaction(
			id: 2,
			asset: $asset,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-01-25'),
			priceDefaultCurrency: new Decimal('30'),
		);

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn($firstTransaction);
		$transactionProvider->method('getTransactions')->willReturn(new ArrayIterator([$tx1, $tx2]));

		$calculator = new DividendDataCalculator($transactionProvider);

		$result = $calculator->getDividendData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), RangeEnum::ThreeMonths);

		// Both transactions fall in January → grouped into one interval
		self::assertCount(1, $result);
		self::assertSame('2024-01-01', $result[0]->interval);
	}

	public function testSumsDividendsForSameAssetInSameInterval(): void
	{
		$firstTransaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable('2024-01-01'),
		);

		$ticker = TickerFixture::getTicker(id: 1, ticker: 'AAPL');
		$asset = AssetFixture::getAsset(id: 1, ticker: $ticker);

		$tx1 = TransactionFixture::getTransaction(
			id: 1,
			asset: $asset,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-01-10'),
			priceDefaultCurrency: new Decimal('40'),
		);
		$tx2 = TransactionFixture::getTransaction(
			id: 2,
			asset: $asset,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-01-20'),
			priceDefaultCurrency: new Decimal('60'),
		);

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn($firstTransaction);
		$transactionProvider->method('getTransactions')->willReturn(new ArrayIterator([$tx1, $tx2]));

		$calculator = new DividendDataCalculator($transactionProvider);

		$result = $calculator->getDividendData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), RangeEnum::ThreeMonths);

		self::assertCount(1, $result);
		self::assertCount(1, $result[0]->dividendDataAssets);
		self::assertSame(100.0, $result[0]->dividendDataAssets[0]->dividendYield->toFloat());
	}

	public function testSeparatesDifferentAssetsInSameInterval(): void
	{
		$firstTransaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable('2024-01-01'),
		);

		$ticker1 = TickerFixture::getTicker(id: 1, ticker: 'AAPL');
		$ticker2 = TickerFixture::getTicker(id: 2, ticker: 'MSFT');
		$asset1 = AssetFixture::getAsset(id: 1, ticker: $ticker1);
		$asset2 = AssetFixture::getAsset(id: 2, ticker: $ticker2);

		$tx1 = TransactionFixture::getTransaction(
			id: 1,
			asset: $asset1,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-01-10'),
			priceDefaultCurrency: new Decimal('40'),
		);
		$tx2 = TransactionFixture::getTransaction(
			id: 2,
			asset: $asset2,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-01-20'),
			priceDefaultCurrency: new Decimal('60'),
		);

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn($firstTransaction);
		$transactionProvider->method('getTransactions')->willReturn(new ArrayIterator([$tx1, $tx2]));

		$calculator = new DividendDataCalculator($transactionProvider);

		$result = $calculator->getDividendData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), RangeEnum::ThreeMonths);

		self::assertCount(1, $result);
		self::assertCount(2, $result[0]->dividendDataAssets);

		$tickers = array_map(static fn (DividendDataAssetDto $a) => $a->tickerTicker, $result[0]->dividendDataAssets);
		self::assertContains('AAPL', $tickers);
		self::assertContains('MSFT', $tickers);
	}

	public function testIntervalsAreSortedByKey(): void
	{
		$firstTransaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable('2024-01-01'),
		);

		$ticker = TickerFixture::getTicker(id: 1);
		$asset = AssetFixture::getAsset(id: 1, ticker: $ticker);

		// Deliberately out of order
		$tx1 = TransactionFixture::getTransaction(
			id: 2,
			asset: $asset,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-03-15'),
			priceDefaultCurrency: new Decimal('20'),
		);
		$tx2 = TransactionFixture::getTransaction(
			id: 1,
			asset: $asset,
			actionType: TransactionActionTypeEnum::Dividend,
			actionCreated: new DateTimeImmutable('2024-01-15'),
			priceDefaultCurrency: new Decimal('10'),
		);

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn($firstTransaction);
		$transactionProvider->method('getTransactions')->willReturn(new ArrayIterator([$tx1, $tx2]));

		$calculator = new DividendDataCalculator($transactionProvider);

		$result = $calculator->getDividendData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), RangeEnum::ThreeMonths);

		self::assertCount(2, $result);
		self::assertSame('2024-01-01', $result[0]->interval);
		self::assertSame('2024-03-01', $result[1]->interval);
	}
}
