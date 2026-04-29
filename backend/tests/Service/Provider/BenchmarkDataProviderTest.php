<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Cache\CacheFactoryInterface;
use FinGather\Service\Cache\CacheTag;
use FinGather\Service\DataCalculator\BenchmarkDataCalculator;
use FinGather\Service\DataCalculator\Dto\BenchmarkDataDto;
use FinGather\Service\Provider\BenchmarkDataProvider;
use FinGather\Service\Provider\ExchangeRateProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\CurrencyFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\DateTimeUtils;
use Nette\Caching\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BenchmarkDataProvider::class)]
#[UsesClass(BenchmarkDataDto::class)]
#[UsesClass(BenchmarkDataCalculator::class)]
#[UsesClass(Cache::class)]
#[UsesClass(CacheTag::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(DateTimeUtils::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(User::class)]
final class BenchmarkDataProviderTest extends TestCase
{
	private User $user;

	private Portfolio $portfolio;

	private Ticker $benchmarkTicker;

	protected function setUp(): void
	{
		$this->user = UserFixture::getUser();
		// Same currency so the calculator path doesn't introduce FX noise.
		$usd = CurrencyFixture::getCurrency();
		$this->portfolio = PortfolioFixture::getPortfolio(currency: $usd);
		$this->benchmarkTicker = TickerFixture::getTicker(id: 99, ticker: 'SPY', currency: $usd);
	}

	public function testGetBenchmarkDataFromDateReturnsZeroUnitsWhenTickerCloseMissing(): void
	{
		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(null);

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);

		$provider = $this->makeProvider($tickerDataProvider, $exchangeRateProvider);

		$result = $provider->getBenchmarkDataFromDate(
			user: $this->user,
			portfolio: $this->portfolio,
			benchmarkTicker: $this->benchmarkTicker,
			benchmarkFromDateTime: new DateTimeImmutable('2024-01-01'),
			portfolioDataValue: new Decimal('1000'),
		);

		self::assertEquals(new Decimal('1000'), $result->value);
		self::assertEquals(new Decimal('0'), $result->units);
	}

	public function testGetBenchmarkDataFromDateConvertsValueWhenCloseAvailable(): void
	{
		// portfolio value 1000, close 100, FX 1.0 → 10 units
		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('100'));

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('1.0'));

		$provider = $this->makeProvider($tickerDataProvider, $exchangeRateProvider);

		$result = $provider->getBenchmarkDataFromDate(
			user: $this->user,
			portfolio: $this->portfolio,
			benchmarkTicker: $this->benchmarkTicker,
			benchmarkFromDateTime: new DateTimeImmutable('2024-01-01'),
			portfolioDataValue: new Decimal('1000'),
		);

		self::assertEquals(new Decimal('1000'), $result->value);
		self::assertEquals(new Decimal('10'), $result->units);
	}

	public function testGetBenchmarkDataReturnsCalculatorResultWhenCacheMisses(): void
	{
		// No transactions, close 200, FX 1.0, fromDateUnits 5 → value = (0 + 5) * 200 * 1 = 1000
		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('200'));

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('1.0'));

		$provider = $this->makeProvider($tickerDataProvider, $exchangeRateProvider);

		$result = $provider->getBenchmarkData(
			user: $this->user,
			portfolio: $this->portfolio,
			benchmarkTicker: $this->benchmarkTicker,
			transactions: [],
			dateTime: new DateTimeImmutable('2024-12-31'),
			benchmarkFromDateTime: new DateTimeImmutable('2024-01-01'),
			benchmarkFromDateUnits: new Decimal('5'),
		);

		self::assertEquals(new Decimal('1000'), $result->value);
		self::assertEquals(new Decimal('5'), $result->units);
	}

	public function testDeleteBenchmarkDataInvokesCacheClean(): void
	{
		// Use the same provider — calling deleteBenchmarkData should not throw and exercises the
		// cache clean path; without an existing entry it's a no-op tag-clean.
		$provider = $this->makeProvider(
			self::createStub(TickerDataProviderInterface::class),
			self::createStub(ExchangeRateProviderInterface::class),
		);

		$provider->deleteBenchmarkData($this->user, $this->portfolio);
		$this->expectNotToPerformAssertions();
	}

	private function makeProvider(
		TickerDataProviderInterface $tickerDataProvider,
		ExchangeRateProviderInterface $exchangeRateProvider,
	): BenchmarkDataProvider {
		$benchmarkDataCalculator = new BenchmarkDataCalculator($exchangeRateProvider, $tickerDataProvider);

		$storage = self::createStub(Storage::class);
		$cache = new Cache($storage, 'test-benchmark-data');

		$cacheFactory = self::createStub(CacheFactoryInterface::class);
		$cacheFactory->method('create')->willReturn($cache);

		return new BenchmarkDataProvider(
			benchmarkDataCalculator: $benchmarkDataCalculator,
			exchangeRateProvider: $exchangeRateProvider,
			tickerDataProvider: $tickerDataProvider,
			cacheFactory: $cacheFactory,
		);
	}
}
