<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\AssetsWithPropertiesDto;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\CountryDto;
use FinGather\Dto\DcaPlanProjectionDto;
use FinGather\Dto\DcaPlanProjectionPointDto;
use FinGather\Dto\IndustryDto;
use FinGather\Dto\MarketDto;
use FinGather\Dto\SectorDto;
use FinGather\Dto\TickerDto;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\StrategyItem;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\DcaPlanDataCalculator;
use FinGather\Service\DataCalculator\Dto\TickerWeightDto;
use FinGather\Service\Provider\AssetWithPropertiesProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\CurrencyFixture;
use FinGather\Tests\Fixtures\Model\Entity\GroupFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerDataFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DcaPlanDataCalculator::class)]
#[UsesClass(TickerWeightDto::class)]
#[UsesClass(DcaPlan::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Group::class)]
#[UsesClass(Strategy::class)]
#[UsesClass(StrategyItem::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(TickerData::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(Currency::class)]
#[UsesClass(AssetWithPropertiesDto::class)]
#[UsesClass(AssetsWithPropertiesDto::class)]
#[UsesClass(DcaPlanProjectionDto::class)]
#[UsesClass(DcaPlanProjectionPointDto::class)]
final class DcaPlanDataCalculatorTest extends TestCase
{
	// ── calculateReturnRate tests ──────────────────────────────────────────────

	public function testCalculateReturnRateForAsset(): void
	{
		// 2025-01-01 to 2026-01-01 = 365 days (2025 is not a leap year)
		// price 100 → 110: CAGR over exactly 1 year = 10%
		$firstData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(110));
		$lastData->id = 2;

		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Asset, asset: $asset);

		$calculator = $this->createCalculator(firstTickerData: $firstData, lastTickerData: $lastData);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(10.0, $returnRate->annual);
		self::assertSame(0.7974140428903764, $returnRate->monthly);
	}

	public function testCalculateReturnRateNoData(): void
	{
		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Asset, asset: $asset);

		$calculator = $this->createCalculator(firstTickerData: null, lastTickerData: null);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(0.0, $returnRate->annual);
		self::assertSame(0.0, $returnRate->monthly);
	}

	public function testCalculateReturnRateForPortfolio(): void
	{
		// Ticker 1: 60% portfolio weight, 100 → 110 over 365 days = 10% CAGR
		// Ticker 2: 40% portfolio weight, 100 → 120 over 365 days = 20% CAGR
		// Weighted average = 60%*10% + 40%*20% = 14%
		$firstData1 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData1 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(110));
		$lastData1->id = 2;

		$firstData2 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData2 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(120));
		$lastData2->id = 2;

		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getFirstTickerData')
			->willReturnCallback(fn (int $tickerId) => match ($tickerId) {
				1 => $firstData1,
				2 => $firstData2,
				default => null,
			});
		$tickerDataProvider->method('getLastTickerData')
			->willReturnCallback(fn (int $tickerId) => match ($tickerId) {
				1 => $lastData1,
				2 => $lastData2,
				default => null,
			});

		$assetDto1 = $this->createAssetWithPropertiesDto(tickerId: 1, percentage: 60.0);
		$assetDto2 = $this->createAssetWithPropertiesDto(tickerId: 2, percentage: 40.0);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProvider::class);
		$assetWithPropertiesProvider->method('getAssetsWithAssetData')
			->willReturn(new AssetsWithPropertiesDto(openAssets: [$assetDto1, $assetDto2], closedAssets: [], watchedAssets: []));

		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Portfolio);

		$calculator = new DcaPlanDataCalculator($tickerDataProvider, $assetWithPropertiesProvider);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(14.0, $returnRate->annual);
		self::assertSame(1.0978851950173452, $returnRate->monthly);
	}

	public function testCalculateReturnRateForGroup(): void
	{
		// Group with one asset: 100 → 150 over 365 days = 50% CAGR
		$firstData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(150));
		$lastData->id = 2;

		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$group = GroupFixture::getGroup(assets: new ArrayIterator([$asset]));
		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Group, group: $group);

		$calculator = $this->createCalculator(firstTickerData: $firstData, lastTickerData: $lastData);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(50.0, $returnRate->annual);
		self::assertSame(3.436608313191658, $returnRate->monthly);
	}

	public function testCalculateReturnRateForStrategy(): void
	{
		// Strategy with one asset item: 100 → 200 over 365 days = 100% CAGR
		$firstData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(200));
		$lastData->id = 2;

		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$strategyItem = new StrategyItem(
			strategy: self::createStub(Strategy::class),
			asset: $asset,
			group: null,
			percentage: new Decimal(100),
		);

		$strategy = new Strategy(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			name: 'Test Strategy',
			isDefault: false,
			strategyItems: new ArrayIterator([$strategyItem]),
		);

		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Strategy, strategy: $strategy);

		$calculator = $this->createCalculator(firstTickerData: $firstData, lastTickerData: $lastData);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(100.0, $returnRate->annual);
		self::assertSame(5.946309435929531, $returnRate->monthly);
	}

	public function testCalculateReturnRateForAssetNegative(): void
	{
		// price 100 → 80 over 365 days → CAGR over 1 year = -20%
		$firstData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(80));
		$lastData->id = 2;

		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Asset, asset: $asset);

		$calculator = $this->createCalculator(firstTickerData: $firstData, lastTickerData: $lastData);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(-20.0, $returnRate->annual);
		self::assertSame(-1.8423470126248342, $returnRate->monthly);
	}

	public function testCalculateReturnRateForPortfolioWithNegative(): void
	{
		// Ticker 1: 50% portfolio weight, 100 → 120 over 365 days = +20%
		// Ticker 2: 50% portfolio weight, 100 → 70  over 365 days = -30%
		// Weighted average = 50%*20% + 50%*(-30%) = -5%
		$firstData1 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData1 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(120));
		$lastData1->id = 2;

		$firstData2 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData2 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(70));
		$lastData2->id = 2;

		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getFirstTickerData')
			->willReturnCallback(fn (int $tickerId) => match ($tickerId) {
				1 => $firstData1,
				2 => $firstData2,
				default => null,
			});
		$tickerDataProvider->method('getLastTickerData')
			->willReturnCallback(fn (int $tickerId) => match ($tickerId) {
				1 => $lastData1,
				2 => $lastData2,
				default => null,
			});

		$assetDto1 = $this->createAssetWithPropertiesDto(tickerId: 1, percentage: 50.0);
		$assetDto2 = $this->createAssetWithPropertiesDto(tickerId: 2, percentage: 50.0);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProvider::class);
		$assetWithPropertiesProvider->method('getAssetsWithAssetData')
			->willReturn(new AssetsWithPropertiesDto(openAssets: [$assetDto1, $assetDto2], closedAssets: [], watchedAssets: []));

		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Portfolio);

		$calculator = new DcaPlanDataCalculator($tickerDataProvider, $assetWithPropertiesProvider);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(-5.0, $returnRate->annual);
		self::assertSame(-0.4265318777560645, $returnRate->monthly);
	}

	public function testCalculateReturnRateForStrategyWeighted(): void
	{
		// Item 1: 70% weight, price 100 → 120 over 365 days = +20% CAGR
		// Item 2: 30% weight, price 100 → 150 over 365 days = +50% CAGR
		// Weighted average = 70%*20% + 30%*50% = 14 + 15 = 29%
		$ticker1 = TickerFixture::getTicker();
		$ticker1->id = 1;

		$ticker2 = TickerFixture::getTicker(ticker: 'MSFT', name: 'Microsoft');
		$ticker2->id = 2;

		$asset1 = AssetFixture::getAsset(ticker: $ticker1);
		$asset2 = AssetFixture::getAsset(ticker: $ticker2);

		$firstData1 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData1 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(120));
		$lastData1->id = 2;

		$firstData2 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData2 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(150));
		$lastData2->id = 2;

		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getFirstTickerData')
			->willReturnCallback(fn (int $tickerId) => match ($tickerId) {
				1 => $firstData1,
				2 => $firstData2,
				default => null,
			});
		$tickerDataProvider->method('getLastTickerData')
			->willReturnCallback(fn (int $tickerId) => match ($tickerId) {
				1 => $lastData1,
				2 => $lastData2,
				default => null,
			});

		$strategyItem1 = new StrategyItem(
			strategy: self::createStub(Strategy::class),
			asset: $asset1,
			group: null,
			percentage: new Decimal(70),
		);
		$strategyItem2 = new StrategyItem(
			strategy: self::createStub(Strategy::class),
			asset: $asset2,
			group: null,
			percentage: new Decimal(30),
		);

		$strategy = new Strategy(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			name: 'Weighted Strategy',
			isDefault: false,
			strategyItems: new ArrayIterator([$strategyItem1, $strategyItem2]),
		);

		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Strategy, strategy: $strategy);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProvider::class);
		$calculator = new DcaPlanDataCalculator($tickerDataProvider, $assetWithPropertiesProvider);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(29.0, $returnRate->annual);
		self::assertSame(2.144693403349862, $returnRate->monthly);
	}

	public function testCalculateReturnRateForStrategyWeightedWithNegative(): void
	{
		// Item 1: 60% weight, price 100 → 80 over 365 days = -20% CAGR
		// Item 2: 40% weight, price 100 → 130 over 365 days = +30% CAGR
		// Weighted average = 60%*(-20%) + 40%*30% = -12 + 12 = 0%
		$ticker1 = TickerFixture::getTicker();
		$ticker1->id = 1;

		$ticker2 = TickerFixture::getTicker(ticker: 'MSFT', name: 'Microsoft');
		$ticker2->id = 2;

		$asset1 = AssetFixture::getAsset(ticker: $ticker1);
		$asset2 = AssetFixture::getAsset(ticker: $ticker2);

		$firstData1 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData1 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(80));
		$lastData1->id = 2;

		$firstData2 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData2 = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(130));
		$lastData2->id = 2;

		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getFirstTickerData')
			->willReturnCallback(fn (int $tickerId) => match ($tickerId) {
				1 => $firstData1,
				2 => $firstData2,
				default => null,
			});
		$tickerDataProvider->method('getLastTickerData')
			->willReturnCallback(fn (int $tickerId) => match ($tickerId) {
				1 => $lastData1,
				2 => $lastData2,
				default => null,
			});

		$strategyItem1 = new StrategyItem(
			strategy: self::createStub(Strategy::class),
			asset: $asset1,
			group: null,
			percentage: new Decimal(60),
		);
		$strategyItem2 = new StrategyItem(
			strategy: self::createStub(Strategy::class),
			asset: $asset2,
			group: null,
			percentage: new Decimal(40),
		);

		$strategy = new Strategy(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			name: 'Weighted Strategy With Negative',
			isDefault: false,
			strategyItems: new ArrayIterator([$strategyItem1, $strategyItem2]),
		);

		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Strategy, strategy: $strategy);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProvider::class);
		$calculator = new DcaPlanDataCalculator($tickerDataProvider, $assetWithPropertiesProvider);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(0.0, $returnRate->annual);
		self::assertSame(0.0, $returnRate->monthly);
	}

	public function testCalculateReturnRateNoAsset(): void
	{
		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Asset, asset: null);

		$calculator = $this->createCalculator(firstTickerData: null, lastTickerData: null);

		$returnRate = $calculator->calculateReturnRate($dcaPlan);
		self::assertSame(0.0, $returnRate->annual);
		self::assertSame(0.0, $returnRate->monthly);
	}

	// ── getProjection tests ────────────────────────────────────────────────────

	public function testGetProjectionZeroReturnRate(): void
	{
		// price unchanged → 0% CAGR → projectedValue equals investedCapital at every point
		$firstData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(100));
		$lastData->id = 2;

		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Asset, asset: $asset);

		$calculator = $this->createCalculator(firstTickerData: $firstData, lastTickerData: $lastData);
		$projection = $calculator->getProjection($dcaPlan, 1);

		self::assertCount(12, $projection->dataPoints);
		foreach ($projection->dataPoints as $i => $point) {
			$expected = 100.0 * ($i + 1);
			self::assertEqualsWithDelta($expected, $point->investedCapital->toFloat(), 0.0001);
			self::assertEqualsWithDelta($expected, $point->projectedValue->toFloat(), 0.0001);
		}
	}

	public function testGetProjectionPositiveReturnRate(): void
	{
		// price 100 → 110 over 365 days → 10% annual CAGR
		// monthlyRate = (1.10)^(1/12) - 1
		// FV[1]  = amount (first deposit, no compounding yet)
		// FV[12] = amount * (1.10 - 1) / monthlyRate  (annuity FV after 12 months)
		$firstData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(110));
		$lastData->id = 2;

		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Asset, asset: $asset);

		$calculator = $this->createCalculator(firstTickerData: $firstData, lastTickerData: $lastData);
		$projection = $calculator->getProjection($dcaPlan, 1);

		self::assertCount(12, $projection->dataPoints);

		// First point: one deposit, no compounding yet → projectedValue == amount
		self::assertEqualsWithDelta(100.0, $projection->dataPoints[0]->projectedValue->toFloat(), 0.0001);

		// All subsequent points: projected must exceed invested
		for ($i = 1; $i < 12; $i++) {
			self::assertGreaterThan(
				$projection->dataPoints[$i]->investedCapital->toFloat(),
				$projection->dataPoints[$i]->projectedValue->toFloat(),
			);
		}

		// Verify last point against the annuity formula
		$monthlyRate = (1 + 10.0 / 100) ** (1 / 12) - 1;
		$expectedFv12 = round(100 * ((1 + $monthlyRate) ** 12 - 1) / $monthlyRate, 8);
		self::assertEqualsWithDelta($expectedFv12, $projection->dataPoints[11]->projectedValue->toFloat(), 0.0001);
	}

	public function testGetProjectionNegativeReturnRate(): void
	{
		// price 100 → 90 over 365 days → -10% annual CAGR
		// All projected values must be less than invested capital (after the first month)
		$firstData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-01-01'), close: new Decimal(100));
		$lastData = TickerDataFixture::getTickerData(date: new DateTimeImmutable('2026-01-01'), close: new Decimal(90));
		$lastData->id = 2;

		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Asset, asset: $asset);

		$calculator = $this->createCalculator(firstTickerData: $firstData, lastTickerData: $lastData);
		$projection = $calculator->getProjection($dcaPlan, 1);

		self::assertCount(12, $projection->dataPoints);

		// First point: one deposit, no compounding yet → projectedValue == amount
		self::assertEqualsWithDelta(100.0, $projection->dataPoints[0]->projectedValue->toFloat(), 0.0001);

		// All subsequent points: projected must be below invested
		for ($i = 1; $i < 12; $i++) {
			self::assertLessThan(
				$projection->dataPoints[$i]->investedCapital->toFloat(),
				$projection->dataPoints[$i]->projectedValue->toFloat(),
			);
		}

		// Verify last point against the annuity formula with negative rate
		$monthlyRate = (1 + (-10.0) / 100) ** (1 / 12) - 1;
		$expectedFv12 = round(100 * ((1 + $monthlyRate) ** 12 - 1) / $monthlyRate, 8);
		self::assertEqualsWithDelta($expectedFv12, $projection->dataPoints[11]->projectedValue->toFloat(), 0.0001);
	}

	public function testGetProjectionHorizonYears(): void
	{
		// No ticker data → 0% rate, just verify correct data-point count per horizon
		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Asset, asset: null);
		$calculator = $this->createCalculator(firstTickerData: null, lastTickerData: null);

		self::assertCount(12, $calculator->getProjection($dcaPlan, 1)->dataPoints);
		self::assertCount(24, $calculator->getProjection($dcaPlan, 2)->dataPoints);
		self::assertCount(120, $calculator->getProjection($dcaPlan, 10)->dataPoints);
	}

	public function testGetProjectionLimitedByEndDate(): void
	{
		// endDate set to ~3 months after startDate → only 3 data points regardless of horizonYears
		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$dcaPlan = $this->createDcaPlan(
			DcaPlanTargetTypeEnum::Asset,
			asset: $asset,
			endDate: new DateTimeImmutable('2026-04-01'),
		);

		$calculator = $this->createCalculator(firstTickerData: null, lastTickerData: null);
		$projection = $calculator->getProjection($dcaPlan, 10);

		self::assertCount(3, $projection->dataPoints);
	}

	public function testGetProjectionWithCurrentValuePortfolio(): void
	{
		// Portfolio target with current value 1000 and 0% return rate
		// Each month: investedCapital = 1000 + 100*n, projectedValue = 1000 + 100*n
		$assetDto = $this->createAssetWithPropertiesDto(tickerId: 1, percentage: 100.0, value: 1000.0);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProvider::class);
		$assetWithPropertiesProvider->method('getAssetsWithAssetData')
			->willReturn(new AssetsWithPropertiesDto(openAssets: [$assetDto], closedAssets: [], watchedAssets: []));

		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getFirstTickerData')->willReturn(null);
		$tickerDataProvider->method('getLastTickerData')->willReturn(null);

		$calculator = new DcaPlanDataCalculator($tickerDataProvider, $assetWithPropertiesProvider);

		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Portfolio);
		$projection = $calculator->getProjection($dcaPlan, horizonYears: 1, withCurrentValue: true);

		self::assertCount(12, $projection->dataPoints);
		foreach ($projection->dataPoints as $i => $point) {
			$expected = 1000.0 + 100.0 * ($i + 1);
			self::assertEqualsWithDelta($expected, $point->investedCapital->toFloat(), 0.0001);
			self::assertEqualsWithDelta($expected, $point->projectedValue->toFloat(), 0.0001);
		}
	}

	public function testGetProjectionWithCurrentValueAsset(): void
	{
		// Asset target, ticker 1 has current value 500; other asset (ticker 2) must be ignored
		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);

		$assetDto1 = $this->createAssetWithPropertiesDto(tickerId: 1, percentage: 60.0, value: 500.0);
		$assetDto2 = $this->createAssetWithPropertiesDto(tickerId: 2, percentage: 40.0, value: 300.0);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProvider::class);
		$assetWithPropertiesProvider->method('getAssetsWithAssetData')
			->willReturn(new AssetsWithPropertiesDto(openAssets: [$assetDto1, $assetDto2], closedAssets: [], watchedAssets: []));

		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getFirstTickerData')->willReturn(null);
		$tickerDataProvider->method('getLastTickerData')->willReturn(null);

		$calculator = new DcaPlanDataCalculator($tickerDataProvider, $assetWithPropertiesProvider);

		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Asset, asset: $asset);
		$projection = $calculator->getProjection($dcaPlan, horizonYears: 1, withCurrentValue: true);

		// Only ticker 1 value (500) is used, ticker 2 (300) is excluded
		// 500 + 100*1
		self::assertEqualsWithDelta(600.0, $projection->dataPoints[0]->investedCapital->toFloat(), 0.0001);
		self::assertEqualsWithDelta(600.0, $projection->dataPoints[0]->projectedValue->toFloat(), 0.0001);
	}

	public function testGetProjectionWithCurrentValueGroup(): void
	{
		// Group target: assets with groupId=1 have value 200+300=500; asset with groupId=2 excluded
		$ticker = TickerFixture::getTicker();
		$ticker->id = 1;
		$asset = AssetFixture::getAsset(ticker: $ticker);
		$group = GroupFixture::getGroup(assets: new ArrayIterator([$asset]));
		$group->id = 1;

		$assetDto1 = $this->createAssetWithPropertiesDto(tickerId: 1, percentage: 40.0, value: 200.0, groupId: 1);
		$assetDto2 = $this->createAssetWithPropertiesDto(tickerId: 2, percentage: 30.0, value: 300.0, groupId: 1);
		$assetDto3 = $this->createAssetWithPropertiesDto(tickerId: 3, percentage: 30.0, value: 400.0, groupId: 2);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProvider::class);
		$assetWithPropertiesProvider->method('getAssetsWithAssetData')
			->willReturn(
				new AssetsWithPropertiesDto(openAssets: [$assetDto1, $assetDto2, $assetDto3], closedAssets: [], watchedAssets: []),
			);

		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getFirstTickerData')->willReturn(null);
		$tickerDataProvider->method('getLastTickerData')->willReturn(null);

		$calculator = new DcaPlanDataCalculator($tickerDataProvider, $assetWithPropertiesProvider);

		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Group, group: $group);
		$projection = $calculator->getProjection($dcaPlan, horizonYears: 1, withCurrentValue: true);

		// Group 1 assets sum = 500; assetDto3 (groupId=2) excluded
		// 500 + 100*1
		self::assertEqualsWithDelta(600.0, $projection->dataPoints[0]->investedCapital->toFloat(), 0.0001);
		self::assertEqualsWithDelta(600.0, $projection->dataPoints[0]->projectedValue->toFloat(), 0.0001);
	}

	public function testGetProjectionWithoutCurrentValue(): void
	{
		// withCurrentValue=false: starts from 0 regardless of portfolio value
		$assetDto = $this->createAssetWithPropertiesDto(tickerId: 1, percentage: 100.0, value: 9999.0);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProvider::class);
		$assetWithPropertiesProvider->method('getAssetsWithAssetData')
			->willReturn(new AssetsWithPropertiesDto(openAssets: [$assetDto], closedAssets: [], watchedAssets: []));

		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getFirstTickerData')->willReturn(null);
		$tickerDataProvider->method('getLastTickerData')->willReturn(null);

		$calculator = new DcaPlanDataCalculator($tickerDataProvider, $assetWithPropertiesProvider);

		$dcaPlan = $this->createDcaPlan(DcaPlanTargetTypeEnum::Portfolio);
		$projection = $calculator->getProjection($dcaPlan, horizonYears: 1, withCurrentValue: false);

		foreach ($projection->dataPoints as $i => $point) {
			$expected = 100.0 * ($i + 1);
			self::assertEqualsWithDelta($expected, $point->investedCapital->toFloat(), 0.0001);
			self::assertEqualsWithDelta($expected, $point->projectedValue->toFloat(), 0.0001);
		}
	}

	// ── helpers ───────────────────────────────────────────────────────────────

	private function createDcaPlan(
		DcaPlanTargetTypeEnum $targetType,
		?Asset $asset = null,
		?Group $group = null,
		?Strategy $strategy = null,
		?DateTimeImmutable $endDate = null,
	): DcaPlan {
		$dcaPlan = new DcaPlan(
			user: UserFixture::getUser(),
			targetType: $targetType,
			portfolio: PortfolioFixture::getPortfolio(),
			asset: $asset,
			group: $group,
			strategy: $strategy,
			amount: new Decimal(100),
			currency: CurrencyFixture::getCurrency(),
			intervalMonths: 1,
			startDate: new DateTimeImmutable('2026-01-01'),
			endDate: $endDate,
			createdAt: new DateTimeImmutable(),
		);
		$dcaPlan->id = 1;

		return $dcaPlan;
	}

	private function createCalculator(?TickerData $firstTickerData, ?TickerData $lastTickerData): DcaPlanDataCalculator
	{
		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getFirstTickerData')->willReturn($firstTickerData);
		$tickerDataProvider->method('getLastTickerData')->willReturn($lastTickerData);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProvider::class);
		$assetWithPropertiesProvider->method('getAssetsWithAssetData')
			->willReturn(new AssetsWithPropertiesDto(openAssets: [], closedAssets: [], watchedAssets: []));

		return new DcaPlanDataCalculator($tickerDataProvider, $assetWithPropertiesProvider);
	}

	private function createAssetWithPropertiesDto(
		int $tickerId,
		float $percentage,
		float $value = 0.0,
		?int $groupId = null,
	): AssetWithPropertiesDto
	{
		$zero = new Decimal(0);

		return new AssetWithPropertiesDto(
			id: 1,
			tickerId: $tickerId,
			ticker: new TickerDto(
				id: $tickerId,
				ticker: 'AAPL',
				name: 'Apple Inc.',
				marketId: 1,
				currencyId: 1,
				type: TickerTypeEnum::Stock,
				isin: null,
				logo: null,
				sector: new SectorDto(id: 1, name: 'Tech'),
				industry: new IndustryDto(id: 1, name: 'Tech'),
				website: null,
				description: null,
				country: new CountryDto(id: 1, isoCode: 'US', isoCode3: 'USA', name: 'United States'),
				market: new MarketDto(
					name: 'NASDAQ',
					acronym: 'NASDAQ',
					mic: 'XNAS',
					exchangeCode: 'NASDAQ',
					country: 'US',
					city: 'New York',
					timezone: 'America/New_York',
					currencyId: 1,
				),
			),
			groupId: $groupId,
			isClosed: false,
			price: $zero,
			units: $zero,
			value: new Decimal((string) $value),
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
			percentage: $percentage,
		);
	}
}
