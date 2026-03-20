<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\BenchmarkDataCalculator;
use FinGather\Service\DataCalculator\Dto\BenchmarkDataDto;
use FinGather\Service\Provider\ExchangeRateProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BenchmarkDataCalculator::class)]
#[UsesClass(BenchmarkDataDto::class)]
final class BenchmarkDataCalculatorTest extends TestCase
{
	private DateTimeImmutable $dateTime;
	private DateTimeImmutable $benchmarkFrom;

	protected function setUp(): void
	{
		$this->dateTime = new DateTimeImmutable('2024-01-10');
		$this->benchmarkFrom = new DateTimeImmutable('2024-01-01');
	}

	public function testReturnsZeroValueWhenNoTickerDataAtDate(): void
	{
		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(null);

		$calculator = new BenchmarkDataCalculator(
			exchangeRateProvider: self::createStub(ExchangeRateProviderInterface::class),
			tickerDataProvider: $tickerDataProvider,
		);

		$result = $calculator->calculate(
			portfolio: PortfolioFixture::getPortfolio(),
			transactions: [],
			benchmarkTicker: TickerFixture::getTicker(),
			dateTime: $this->dateTime,
			benchmarkFromDateTime: $this->benchmarkFrom,
			benchmarkFromDateUnits: new Decimal(5),
		);

		self::assertSame(0.0, $result->value->toFloat());
		// units are still accumulated even when ticker data is missing at dateTime
		self::assertSame(0.0, $result->units->toFloat());
	}

	public function testExcludesTransactionsBeforeBenchmarkFrom(): void
	{
		$transaction = TransactionFixture::getTransaction(
			id: 1,
			actionCreated: new DateTimeImmutable('2023-12-31'), // before benchmarkFrom
			units: new Decimal(10),
			priceDefaultCurrency: new Decimal(100),
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('150'));

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('1'));

		$calculator = new BenchmarkDataCalculator(
			exchangeRateProvider: $exchangeRateProvider,
			tickerDataProvider: $tickerDataProvider,
		);

		$result = $calculator->calculate(
			portfolio: PortfolioFixture::getPortfolio(),
			transactions: [$transaction],
			benchmarkTicker: TickerFixture::getTicker(),
			dateTime: $this->dateTime,
			benchmarkFromDateTime: $this->benchmarkFrom,
			benchmarkFromDateUnits: new Decimal(5),
		);

		// Transaction is excluded; only benchmarkFromDateUnits=5 contribute
		self::assertSame(5.0, $result->units->toFloat());
		self::assertSame(750.0, $result->value->toFloat()); // 5 * 150 * 1
	}

	public function testExcludesTransactionsAfterDateTime(): void
	{
		$transaction = TransactionFixture::getTransaction(
			id: 1,
			actionCreated: new DateTimeImmutable('2024-01-15'), // after dateTime
			units: new Decimal(10),
			priceDefaultCurrency: new Decimal(100),
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('150'));

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('1'));

		$calculator = new BenchmarkDataCalculator(
			exchangeRateProvider: $exchangeRateProvider,
			tickerDataProvider: $tickerDataProvider,
		);

		$result = $calculator->calculate(
			portfolio: PortfolioFixture::getPortfolio(),
			transactions: [$transaction],
			benchmarkTicker: TickerFixture::getTicker(),
			dateTime: $this->dateTime,
			benchmarkFromDateTime: $this->benchmarkFrom,
			benchmarkFromDateUnits: new Decimal(2),
		);

		// Loop breaks; only benchmarkFromDateUnits=2 contribute
		self::assertSame(2.0, $result->units->toFloat());
		self::assertSame(300.0, $result->value->toFloat()); // 2 * 150 * 1
	}

	public function testCalculatesUnitsAndValueForSingleTransaction(): void
	{
		// Transaction at 2024-01-05, cost = 10 units * $100 = $1000
		// Benchmark price at transaction date = $50  → buys 1000/50 = 20 benchmark units
		// Benchmark price at dateTime = $60
		// Exchange rate = 1
		// Expected: units=20, value=20*60*1=1200

		$transaction = TransactionFixture::getTransaction(
			id: 1,
			actionCreated: new DateTimeImmutable('2024-01-05'),
			units: new Decimal(10),
			priceDefaultCurrency: new Decimal(100),
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')
			->willReturnCallback(static function (mixed $ticker, DateTimeImmutable $date): Decimal {
				return $date->format('Y-m-d') === '2024-01-05'
					? new Decimal('50')   // benchmark price at transaction date
					: new Decimal('60');  // benchmark price at main dateTime
			});

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('1'));

		$calculator = new BenchmarkDataCalculator(
			exchangeRateProvider: $exchangeRateProvider,
			tickerDataProvider: $tickerDataProvider,
		);

		$result = $calculator->calculate(
			portfolio: PortfolioFixture::getPortfolio(),
			transactions: [$transaction],
			benchmarkTicker: TickerFixture::getTicker(),
			dateTime: $this->dateTime,
			benchmarkFromDateTime: $this->benchmarkFrom,
			benchmarkFromDateUnits: new Decimal(0),
		);

		self::assertSame(20.0, $result->units->toFloat());
		self::assertSame(1200.0, $result->value->toFloat());
	}

	public function testAddsFromDateUnitsToSum(): void
	{
		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('100'));

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('1'));

		$calculator = new BenchmarkDataCalculator(
			exchangeRateProvider: $exchangeRateProvider,
			tickerDataProvider: $tickerDataProvider,
		);

		$result = $calculator->calculate(
			portfolio: PortfolioFixture::getPortfolio(),
			transactions: [],
			benchmarkTicker: TickerFixture::getTicker(),
			dateTime: $this->dateTime,
			benchmarkFromDateTime: $this->benchmarkFrom,
			benchmarkFromDateUnits: new Decimal(7),
		);

		self::assertSame(7.0, $result->units->toFloat());
		self::assertSame(700.0, $result->value->toFloat()); // 7 * 100 * 1
	}

	public function testTransactionWithNoTickerDataContributesZeroUnits(): void
	{
		$transaction = TransactionFixture::getTransaction(
			id: 1,
			actionCreated: new DateTimeImmutable('2024-01-05'),
			units: new Decimal(10),
			priceDefaultCurrency: new Decimal(100),
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')
			->willReturnCallback(static function (mixed $ticker, DateTimeImmutable $date): ?Decimal {
				// No data at transaction date; data available at main dateTime
				return $date->format('Y-m-d') === '2024-01-05'
					? null
					: new Decimal('100');
			});

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('1'));

		$calculator = new BenchmarkDataCalculator(
			exchangeRateProvider: $exchangeRateProvider,
			tickerDataProvider: $tickerDataProvider,
		);

		$result = $calculator->calculate(
			portfolio: PortfolioFixture::getPortfolio(),
			transactions: [$transaction],
			benchmarkTicker: TickerFixture::getTicker(),
			dateTime: $this->dateTime,
			benchmarkFromDateTime: $this->benchmarkFrom,
			benchmarkFromDateUnits: new Decimal(0),
		);

		self::assertSame(0.0, $result->units->toFloat());
		self::assertSame(0.0, $result->value->toFloat());
	}

	public function testExchangeRateAppliedToValue(): void
	{
		// Exchange rate = 2 (e.g. EUR→USD), benchmark close = 100
		// fromDateUnits = 3 → value = 3 * 100 * 2 = 600

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('100'));

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('2'));

		$calculator = new BenchmarkDataCalculator(
			exchangeRateProvider: $exchangeRateProvider,
			tickerDataProvider: $tickerDataProvider,
		);

		$result = $calculator->calculate(
			portfolio: PortfolioFixture::getPortfolio(),
			transactions: [],
			benchmarkTicker: TickerFixture::getTicker(),
			dateTime: $this->dateTime,
			benchmarkFromDateTime: $this->benchmarkFrom,
			benchmarkFromDateUnits: new Decimal(3),
		);

		self::assertSame(600.0, $result->value->toFloat());
	}
}
