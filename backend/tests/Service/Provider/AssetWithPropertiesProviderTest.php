<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\AssetDto;
use FinGather\Dto\AssetsWithPropertiesDto;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\CountryDto;
use FinGather\Dto\Enum\AssetOrderEnum;
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
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\AssetWithPropertiesProvider;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\GroupFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AssetWithPropertiesProvider::class)]
#[UsesClass(Asset::class)]
#[UsesClass(AssetDataDto::class)]
#[UsesClass(AssetDto::class)]
#[UsesClass(AssetWithPropertiesDto::class)]
#[UsesClass(AssetsWithPropertiesDto::class)]
#[UsesClass(CalculatedDataDto::class)]
#[UsesClass(CalculatorUtils::class)]
#[UsesClass(Country::class)]
#[UsesClass(CountryDto::class)]
#[UsesClass(Currency::class)]
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
final class AssetWithPropertiesProviderTest extends TestCase
{
	private User $user;

	private Portfolio $portfolio;

	protected function setUp(): void
	{
		$this->user = UserFixture::getUser();
		$this->portfolio = PortfolioFixture::getPortfolio();
	}

	public function testPartitionsAssetsIntoOpenClosedAndWatched(): void
	{
		// Asset 1 → open (units > 0), Asset 2 → closed (units = 0), Asset 3 → watched (no asset data)
		$openAsset = $this->makeAsset(id: 1, tickerSymbol: 'AAPL');
		$closedAsset = $this->makeAsset(id: 2, tickerSymbol: 'MSFT');
		$watchedAsset = $this->makeAsset(id: 3, tickerSymbol: 'GOOG');

		$portfolioValue = new Decimal('1000');
		$openValue = new Decimal('600');
		$closedValue = new Decimal('0');

		$provider = $this->makeProvider(
			portfolioValue: $portfolioValue,
			assets: [$openAsset, $closedAsset, $watchedAsset],
			assetDataMap: [
				1 => $this->makeAssetDataDto(units: new Decimal('5'), value: $openValue),
				2 => $this->makeAssetDataDto(units: new Decimal('0'), value: $closedValue),
				// asset 3 → null (watched)
			],
			watchedTickerCloseMap: [3 => new Decimal('150')],
		);

		$result = $provider->getAssetsWithAssetData($this->user, $this->portfolio, new DateTimeImmutable(), AssetOrderEnum::TickerName);

		self::assertCount(1, $result->openAssets);
		self::assertSame(1, $result->openAssets[0]->id);
		// 600 / 1000 = 60.0
		self::assertSame(60.0, $result->openAssets[0]->percentage);

		self::assertCount(1, $result->closedAssets);
		self::assertSame(2, $result->closedAssets[0]->id);
		self::assertTrue($result->closedAssets[0]->isClosed);

		self::assertCount(1, $result->watchedAssets);
		self::assertSame(3, $result->watchedAssets[0]->id);
		self::assertEquals(new Decimal('150'), $result->watchedAssets[0]->price);
	}

	public function testTickerNameOrderSortsAlphabetically(): void
	{
		$msft = $this->makeAsset(id: 1, tickerSymbol: 'MSFT');
		$aapl = $this->makeAsset(id: 2, tickerSymbol: 'AAPL');
		$goog = $this->makeAsset(id: 3, tickerSymbol: 'GOOG');

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$msft, $aapl, $goog],
			assetDataMap: [
				1 => $this->makeAssetDataDto(units: new Decimal('1'), value: new Decimal('100')),
				2 => $this->makeAssetDataDto(units: new Decimal('1'), value: new Decimal('100')),
				3 => $this->makeAssetDataDto(units: new Decimal('1'), value: new Decimal('100')),
			],
		);

		$result = $provider->getAssetsWithAssetData($this->user, $this->portfolio, new DateTimeImmutable(), AssetOrderEnum::TickerName);

		self::assertSame(
			['AAPL', 'GOOG', 'MSFT'],
			array_map(static fn (AssetWithPropertiesDto $a): string => $a->ticker->ticker, $result->openAssets),
		);
	}

	public function testValueOrderSortsDescending(): void
	{
		$smallAsset = $this->makeAsset(id: 1, tickerSymbol: 'AAA');
		$bigAsset = $this->makeAsset(id: 2, tickerSymbol: 'BBB');
		$midAsset = $this->makeAsset(id: 3, tickerSymbol: 'CCC');

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$smallAsset, $bigAsset, $midAsset],
			assetDataMap: [
				1 => $this->makeAssetDataDto(units: new Decimal('1'), value: new Decimal('100')),
				2 => $this->makeAssetDataDto(units: new Decimal('1'), value: new Decimal('500')),
				3 => $this->makeAssetDataDto(units: new Decimal('1'), value: new Decimal('300')),
			],
		);

		$result = $provider->getAssetsWithAssetData($this->user, $this->portfolio, new DateTimeImmutable(), AssetOrderEnum::Value);

		self::assertSame([2, 3, 1], array_map(static fn (AssetWithPropertiesDto $a): int => $a->id, $result->openAssets));
	}

	public function testGainOrderSortsByDefaultCurrencyDescending(): void
	{
		$asset1 = $this->makeAsset(id: 1, tickerSymbol: 'AAA');
		$asset2 = $this->makeAsset(id: 2, tickerSymbol: 'BBB');

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset1, $asset2],
			assetDataMap: [
				1 => $this->makeAssetDataDto(units: new Decimal('1'), value: new Decimal('100'), gainDefaultCurrency: new Decimal('5')),
				2 => $this->makeAssetDataDto(units: new Decimal('1'), value: new Decimal('100'), gainDefaultCurrency: new Decimal('50')),
			],
		);

		$result = $provider->getAssetsWithAssetData($this->user, $this->portfolio, new DateTimeImmutable(), AssetOrderEnum::Gain);

		self::assertSame([2, 1], array_map(static fn (AssetWithPropertiesDto $a): int => $a->id, $result->openAssets));
	}

	private function makeAsset(int $id, string $tickerSymbol): Asset
	{
		$ticker = TickerFixture::getTicker(id: $id, ticker: $tickerSymbol);
		$group = GroupFixture::getGroup();
		$group->id = 1;
		return AssetFixture::getAsset(id: $id, ticker: $ticker, group: $group);
	}

	/**
	 * @param list<Asset> $assets
	 * @param array<int, AssetDataDto> $assetDataMap Asset id → data; missing → null (watched).
	 * @param array<int, Decimal> $watchedTickerCloseMap Ticker id → close price for watched assets.
	 */
	private function makeProvider(
		Decimal $portfolioValue,
		array $assets,
		array $assetDataMap,
		array $watchedTickerCloseMap = [],
	): AssetWithPropertiesProvider {
		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makeCalculatedDataDto($portfolioValue));

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssetsWithTickerRelations')->willReturn($assets);

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('getAssetData')->willReturnCallback(
			static fn (User $user, Portfolio $portfolio, Asset $asset): ?AssetDataDto => $assetDataMap[$asset->id] ?? null,
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturnCallback(
			static fn (Ticker $ticker): ?Decimal => $watchedTickerCloseMap[$ticker->id] ?? null,
		);

		return new AssetWithPropertiesProvider(
			assetProvider: $assetProvider,
			assetDataProvider: $assetDataProvider,
			tickerDataProvider: $tickerDataProvider,
			portfolioDataProvider: $portfolioDataProvider,
		);
	}

	private function makeAssetDataDto(Decimal $units, Decimal $value, Decimal $gainDefaultCurrency = new Decimal('0'),): AssetDataDto
	{
		$zero = new Decimal('0');
		return new AssetDataDto(
			date: new DateTimeImmutable(),
			price: $zero,
			units: $units,
			value: $value,
			transactionValue: $zero,
			transactionValueDefaultCurrency: $zero,
			averagePrice: $zero,
			averagePriceDefaultCurrency: $zero,
			gain: $zero,
			gainDefaultCurrency: $gainDefaultCurrency,
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

	private function makeCalculatedDataDto(Decimal $value): CalculatedDataDto
	{
		$zero = new Decimal('0');
		return new CalculatedDataDto(
			date: new DateTimeImmutable(),
			value: $value,
			transactionValue: $zero,
			gain: $zero,
			gainPercentage: 0.0,
			gainPercentagePerAnnum: 0.0,
			realizedGain: $zero,
			dividendYield: $zero,
			dividendYieldPercentage: 0.0,
			dividendYieldPercentagePerAnnum: 0.0,
			fxImpact: $zero,
			fxImpactPercentage: 0.0,
			fxImpactPercentagePerAnnum: 0.0,
			return: $zero,
			returnPercentage: 0.0,
			returnPercentagePerAnnum: 0.0,
			tax: $zero,
			fee: $zero,
		);
	}
}
