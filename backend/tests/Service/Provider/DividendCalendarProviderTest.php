<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\CountryDto;
use FinGather\Dto\DividendCalendarItemDto;
use FinGather\Dto\IndustryDto;
use FinGather\Dto\MarketDto;
use FinGather\Dto\SectorDto;
use FinGather\Dto\TickerDto;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Cache\CacheFactoryInterface;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\DividendCalendarProvider;
use FinGather\Service\Provider\ExchangeRateProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\MarketFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\DateTimeUtils;
use MarekSkopal\TwelveData\Api\Fundamentals;
use MarekSkopal\TwelveData\Dto\Fundamentals\DividendsCalendar;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;
use Nette\Caching\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionProperty;

#[CoversClass(DividendCalendarProvider::class)]
#[UsesClass(Asset::class)]
#[UsesClass(AssetDataDto::class)]
#[UsesClass(Cache::class)]
#[UsesClass(Country::class)]
#[UsesClass(CountryDto::class)]
#[UsesClass(Currency::class)]
#[UsesClass(DateTimeUtils::class)]
#[UsesClass(DividendCalendarItemDto::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(IndustryDto::class)]
#[UsesClass(Market::class)]
#[UsesClass(MarketDto::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(SectorDto::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(TickerDto::class)]
#[UsesClass(User::class)]
final class DividendCalendarProviderTest extends TestCase
{
	private User $user;

	private Portfolio $portfolio;

	private Asset $healthyAsset;

	private Asset $brokenAsset;

	protected function setUp(): void
	{
		$this->user = UserFixture::getUser();
		$this->portfolio = PortfolioFixture::getPortfolio();

		$market = MarketFixture::getMarket(mic: 'XNAS');
		$this->healthyAsset = AssetFixture::getAsset(id: 1, ticker: TickerFixture::getTicker(id: 1, ticker: 'AAPL', market: $market));
		$this->brokenAsset = AssetFixture::getAsset(id: 2, ticker: TickerFixture::getTicker(id: 2, ticker: 'BRKN', market: $market));
	}

	public function testReturnsCalendarItemsForOpenAssets(): void
	{
		$fundamentals = self::createStub(Fundamentals::class);
		$fundamentals->method('dividendsCalendar')->willReturn([
			new DividendsCalendar(
				symbol: 'AAPL',
				micCode: 'XNAS',
				exchange: 'NASDAQ',
				exDate: new DateTimeImmutable('2026-10-01'),
				amount: 0.25,
			),
		]);

		$provider = $this->makeProvider(
			fundamentals: $fundamentals,
			assets: [$this->healthyAsset],
			logger: self::createStub(LoggerInterface::class),
		);

		$items = $provider->getDividendCalendar($this->user, $this->portfolio);

		self::assertCount(1, $items);
		self::assertSame(1, $items[0]->assetId);
		self::assertEquals(new Decimal('0.25'), $items[0]->amountPerShare);
		// 0.25 per share * 10 units * exchange rate 2
		self::assertEquals(new Decimal('2.5'), $items[0]->totalAmount);
		self::assertEquals(new Decimal('5'), $items[0]->totalAmountDefaultCurrency);
	}

	public function testTickerNotFoundOnTwelveDataYieldsNoItems(): void
	{
		$fundamentals = self::createStub(Fundamentals::class);
		$fundamentals->method('dividendsCalendar')->willThrowException(new NotFoundException('not found'));

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->never())->method('warning');

		$provider = $this->makeProvider(fundamentals: $fundamentals, assets: [$this->healthyAsset], logger: $logger);

		self::assertSame([], $provider->getDividendCalendar($this->user, $this->portfolio));
	}

	public function testUnparseableTwelveDataResponseForOneTickerDoesNotAbortCalendar(): void
	{
		$fundamentals = self::createStub(Fundamentals::class);
		$fundamentals->method('dividendsCalendar')->willReturnCallback(
			static function (?string $symbol): array {
				if ($symbol === 'BRKN') {
					throw new \TypeError('array_map(): Argument #2 ($array) must be of type array, null given');
				}

				return [
					new DividendsCalendar(
						symbol: 'AAPL',
						micCode: 'XNAS',
						exchange: 'NASDAQ',
						exDate: new DateTimeImmutable('2026-10-01'),
						amount: 0.25,
					),
				];
			},
		);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('warning')
			->with(self::stringContains('BRKN'));

		$provider = $this->makeProvider(fundamentals: $fundamentals, assets: [$this->brokenAsset, $this->healthyAsset], logger: $logger);

		$items = $provider->getDividendCalendar($this->user, $this->portfolio);

		self::assertCount(1, $items);
		self::assertSame(1, $items[0]->assetId);
	}

	/** @param list<Asset> $assets */
	private function makeProvider(Fundamentals $fundamentals, array $assets, LoggerInterface $logger): DividendCalendarProvider
	{
		$twelveData = self::createStub(TwelveData::class);
		new ReflectionProperty(TwelveData::class, 'fundamentals')->setValue($twelveData, $fundamentals);

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn($assets);

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('getAssetData')->willReturn($this->makeAssetDataDto(units: new Decimal('10')));

		$exchangeRateProvider = self::createStub(ExchangeRateProviderInterface::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('2'));

		$cacheFactory = self::createStub(CacheFactoryInterface::class);
		$cacheFactory->method('create')->willReturn(new Cache(self::createStub(Storage::class), 'test-dividend-calendar'));

		return new DividendCalendarProvider(
			assetProvider: $assetProvider,
			assetDataProvider: $assetDataProvider,
			exchangeRateProvider: $exchangeRateProvider,
			twelveData: $twelveData,
			logger: $logger,
			cacheFactory: $cacheFactory,
		);
	}

	private function makeAssetDataDto(Decimal $units): AssetDataDto
	{
		$zero = new Decimal('0');
		return new AssetDataDto(
			date: new DateTimeImmutable(),
			price: $zero,
			units: $units,
			value: $zero,
			transactionValue: $zero,
			transactionValueDefaultCurrency: $zero,
			averagePrice: $zero,
			averagePriceDefaultCurrency: $zero,
			gain: $zero,
			gainDefaultCurrency: $zero,
			realizedGain: $zero,
			realizedGainDefaultCurrency: $zero,
			gainPercentage: 0.0,
			gainPercentagePerAnnum: 0.0,
			dividendYield: $zero,
			dividendYieldDefaultCurrency: $zero,
			dividendYieldPercentage: 0.0,
			dividendYieldPercentagePerAnnum: 0.0,
			fxImpact: $zero,
			fxImpactPercentage: 0.0,
			fxImpactPercentagePerAnnum: 0.0,
			return: $zero,
			returnPercentage: 0.0,
			returnPercentagePerAnnum: 0.0,
			tax: $zero,
			taxDefaultCurrency: $zero,
			fee: $zero,
			feeDefaultCurrency: $zero,
			firstTransactionActionCreated: new DateTimeImmutable(),
		);
	}
}
