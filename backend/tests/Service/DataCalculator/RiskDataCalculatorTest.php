<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Model\Entity\Ticker;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\DataCalculator\Dto\RiskDataDto;
use FinGather\Service\DataCalculator\RiskDataCalculator;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\Dto\TickerDataAdjustedDto;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(RiskDataCalculator::class)]
#[UsesClass(RiskDataDto::class)]
final class RiskDataCalculatorTest extends TestCase
{
	private PortfolioDataProviderInterface&Stub $portfolioDataProvider;

	private AssetProviderInterface&Stub $assetProvider;

	private AssetDataProviderInterface&Stub $assetDataProvider;

	private TickerDataProviderInterface&Stub $tickerDataProvider;

	private TransactionProviderInterface&Stub $transactionProvider;

	private RiskDataCalculator $calculator;

	protected function setUp(): void
	{
		$this->portfolioDataProvider = $this::createStub(PortfolioDataProviderInterface::class);
		$this->assetProvider = $this::createStub(AssetProviderInterface::class);
		$this->assetDataProvider = $this::createStub(AssetDataProviderInterface::class);
		$this->tickerDataProvider = $this::createStub(TickerDataProviderInterface::class);
		$this->transactionProvider = $this::createStub(TransactionProviderInterface::class);

		$this->calculator = new RiskDataCalculator(
			portfolioDataProvider: $this->portfolioDataProvider,
			assetProvider: $this->assetProvider,
			assetDataProvider: $this->assetDataProvider,
			tickerDataProvider: $this->tickerDataProvider,
			transactionProvider: $this->transactionProvider,
		);
	}

	public function testCalculateReturnsZeroWhenNoTransactions(): void
	{
		$this->transactionProvider->method('getFirstTransaction')->willReturn(null);

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::OneYear,
			benchmarkTicker: null,
			customRangeFrom: null,
			customRangeTo: null,
		);

		self::assertSame(0.0, $result->volatility);
		self::assertSame(0.0, $result->maxDrawdown);
		self::assertSame(0.0, $result->sharpeRatio);
		self::assertSame(0.0, $result->beta);
		self::assertSame([], $result->correlationLabels);
		self::assertSame([], $result->correlationMatrix);
	}

	public function testCalculateReturnsZeroWithOnlyOneDataPoint(): void
	{
		$from = new DateTimeImmutable('2024-01-01');
		$to = new DateTimeImmutable('2024-01-01');

		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: $from),
		);
		$this->portfolioDataProvider->method('getPortfolioData')->willReturn(
			$this->makeCalculatedData(value: 100.0),
		);
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::Custom,
			benchmarkTicker: null,
			customRangeFrom: $from,
			customRangeTo: $to,
		);

		self::assertSame(0.0, $result->volatility);
		self::assertSame(0.0, $result->maxDrawdown);
		self::assertSame(0.0, $result->sharpeRatio);
		self::assertSame(0.0, $result->beta);
	}

	public function testCalculateVolatilityIsPositiveForVaryingValues(): void
	{
		$from = new DateTimeImmutable('2024-01-01');
		$to = new DateTimeImmutable('2024-01-05');

		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: $from),
		);

		// Alternating values produce non-zero stddev
		$this->portfolioDataProvider->method('getPortfolioData')->willReturnOnConsecutiveCalls(
			$this->makeCalculatedData(value: 100.0),
			$this->makeCalculatedData(value: 110.0),
			$this->makeCalculatedData(value: 95.0),
			$this->makeCalculatedData(value: 115.0),
			$this->makeCalculatedData(value: 105.0),
		);
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::Custom,
			benchmarkTicker: null,
			customRangeFrom: $from,
			customRangeTo: $to,
		);

		self::assertGreaterThan(0.0, $result->volatility);
	}

	public function testCalculateMaxDrawdown(): void
	{
		$from = new DateTimeImmutable('2024-01-01');
		$to = new DateTimeImmutable('2024-01-04');

		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: $from),
		);

		// 100 → 120 → 80 → 100: max drawdown = (80 - 120) / 120 * 100 = -33.33...%
		$this->portfolioDataProvider->method('getPortfolioData')->willReturnOnConsecutiveCalls(
			$this->makeCalculatedData(value: 100.0),
			$this->makeCalculatedData(value: 120.0),
			$this->makeCalculatedData(value: 80.0),
			$this->makeCalculatedData(value: 100.0),
		);
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::Custom,
			benchmarkTicker: null,
			customRangeFrom: $from,
			customRangeTo: $to,
		);

		self::assertEqualsWithDelta(-33.3333, $result->maxDrawdown, 0.001);
	}

	public function testCalculateSharpeRatioIsPositiveWhenReturnIsPositive(): void
	{
		$from = new DateTimeImmutable('2024-01-01');
		$to = new DateTimeImmutable('2024-01-05');

		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: $from),
		);

		$this->portfolioDataProvider->method('getPortfolioData')->willReturnOnConsecutiveCalls(
			$this->makeCalculatedData(value: 100.0, returnPercentagePerAnnum: 20.0),
			$this->makeCalculatedData(value: 105.0, returnPercentagePerAnnum: 20.0),
			$this->makeCalculatedData(value: 102.0, returnPercentagePerAnnum: 20.0),
			$this->makeCalculatedData(value: 108.0, returnPercentagePerAnnum: 20.0),
			$this->makeCalculatedData(value: 110.0, returnPercentagePerAnnum: 20.0),
		);
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::Custom,
			benchmarkTicker: null,
			customRangeFrom: $from,
			customRangeTo: $to,
		);

		self::assertGreaterThan(0.0, $result->sharpeRatio);
	}

	public function testCalculateBetaIsZeroWhenNoBenchmark(): void
	{
		$from = new DateTimeImmutable('2024-01-01');
		$to = new DateTimeImmutable('2024-01-03');

		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: $from),
		);
		$this->portfolioDataProvider->method('getPortfolioData')->willReturnOnConsecutiveCalls(
			$this->makeCalculatedData(value: 100.0),
			$this->makeCalculatedData(value: 105.0),
			$this->makeCalculatedData(value: 110.0),
		);
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::Custom,
			benchmarkTicker: null,
			customRangeFrom: $from,
			customRangeTo: $to,
		);

		self::assertSame(0.0, $result->beta);
	}

	public function testCalculateBetaIsNonZeroWithBenchmark(): void
	{
		$from = new DateTimeImmutable('2024-01-01');
		$to = new DateTimeImmutable('2024-01-05');

		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: $from),
		);
		$this->portfolioDataProvider->method('getPortfolioData')->willReturnOnConsecutiveCalls(
			$this->makeCalculatedData(value: 100.0),
			$this->makeCalculatedData(value: 102.0),
			$this->makeCalculatedData(value: 105.0),
			$this->makeCalculatedData(value: 103.0),
			$this->makeCalculatedData(value: 107.0),
		);
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$benchmarkTicker = TickerFixture::getTicker();
		$this->tickerDataProvider->method('getAdjustedTickerDatas')->willReturn([
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-01'), new Decimal('100')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-02'), new Decimal('101')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-03'), new Decimal('103')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-04'), new Decimal('102')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-05'), new Decimal('104')),
		]);

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::Custom,
			benchmarkTicker: $benchmarkTicker,
			customRangeFrom: $from,
			customRangeTo: $to,
		);

		self::assertNotSame(0.0, $result->beta);
	}

	public function testCalculateCorrelationMatrixDiagonalIsOne(): void
	{
		$from = new DateTimeImmutable('2024-01-01');
		$to = new DateTimeImmutable('2024-01-05');

		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: $from),
		);
		$this->portfolioDataProvider->method('getPortfolioData')->willReturn(
			$this->makeCalculatedData(value: 100.0),
		);

		$ticker1 = TickerFixture::getTicker(id: 1, ticker: 'AAPL');
		$ticker2 = TickerFixture::getTicker(id: 2, ticker: 'MSFT');
		$asset1 = AssetFixture::getAsset(id: 1, ticker: $ticker1);
		$asset2 = AssetFixture::getAsset(id: 2, ticker: $ticker2);

		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([$asset1, $asset2]));

		$openAssetData = $this->makeAssetDataDto(units: new Decimal('10'), value: new Decimal('1000'));
		$this->assetDataProvider->method('getAssetData')->willReturn($openAssetData);

		$dates = [
			new DateTimeImmutable('2024-01-01'),
			new DateTimeImmutable('2024-01-02'),
			new DateTimeImmutable('2024-01-03'),
			new DateTimeImmutable('2024-01-04'),
			new DateTimeImmutable('2024-01-05'),
		];
		$prices1 = [
			$this->makeAdjustedTickerData($dates[0], new Decimal('100')),
			$this->makeAdjustedTickerData($dates[1], new Decimal('102')),
			$this->makeAdjustedTickerData($dates[2], new Decimal('105')),
			$this->makeAdjustedTickerData($dates[3], new Decimal('103')),
			$this->makeAdjustedTickerData($dates[4], new Decimal('107')),
		];
		$prices2 = [
			$this->makeAdjustedTickerData($dates[0], new Decimal('50')),
			$this->makeAdjustedTickerData($dates[1], new Decimal('51')),
			$this->makeAdjustedTickerData($dates[2], new Decimal('49')),
			$this->makeAdjustedTickerData($dates[3], new Decimal('52')),
			$this->makeAdjustedTickerData($dates[4], new Decimal('53')),
		];

		$this->tickerDataProvider->method('getAdjustedTickerDatas')->willReturnOnConsecutiveCalls($prices1, $prices2);

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::Custom,
			benchmarkTicker: null,
			customRangeFrom: $from,
			customRangeTo: $to,
		);

		self::assertCount(2, $result->correlationLabels);
		self::assertCount(2, $result->correlationMatrix);
		// Diagonal must be correlation of series with itself = 1.0
		self::assertEqualsWithDelta(1.0, $result->correlationMatrix[0][0], 0.0001);
		self::assertEqualsWithDelta(1.0, $result->correlationMatrix[1][1], 0.0001);
	}

	public function testCorrelationAlignsTickersOnCommonCalendarDates(): void
	{
		$from = new DateTimeImmutable('2024-01-01');
		$to = new DateTimeImmutable('2024-01-10');

		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: $from),
		);
		$this->portfolioDataProvider->method('getPortfolioData')->willReturn(
			$this->makeCalculatedData(value: 100.0),
		);

		$ticker1 = TickerFixture::getTicker(id: 1, ticker: 'STOCK');
		$ticker2 = TickerFixture::getTicker(id: 2, ticker: 'CRYPTO');
		$asset1 = AssetFixture::getAsset(id: 1, ticker: $ticker1);
		$asset2 = AssetFixture::getAsset(id: 2, ticker: $ticker2);

		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([$asset1, $asset2]));
		$this->assetDataProvider->method('getAssetData')->willReturn(
			$this->makeAssetDataDto(units: new Decimal('10'), value: new Decimal('1000')),
		);

		// STOCK trades only on weekdays (Mon-Fri).
		$stockPrices = [
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-01'), new Decimal('100')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-02'), new Decimal('102')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-03'), new Decimal('104')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-04'), new Decimal('103')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-05'), new Decimal('105')),
		];

		// CRYPTO trades every day, perfectly matches STOCK on shared trading days,
		// but has extra weekend rows that would shift naive index-aligned correlation.
		$cryptoPrices = [
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-01'), new Decimal('50')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-02'), new Decimal('51')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-03'), new Decimal('52')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-04'), new Decimal('51.5')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-05'), new Decimal('52.5')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-06'), new Decimal('48')),
			$this->makeAdjustedTickerData(new DateTimeImmutable('2024-01-07'), new Decimal('49')),
		];

		$this->tickerDataProvider->method('getAdjustedTickerDatas')->willReturnOnConsecutiveCalls($stockPrices, $cryptoPrices);

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::Custom,
			benchmarkTicker: null,
			customRangeFrom: $from,
			customRangeTo: $to,
		);

		self::assertCount(2, $result->correlationLabels);
		// STOCK and CRYPTO move in lock-step on the 5 shared weekday dates,
		// so post-alignment correlation must be ~+1, not deflated by the extra weekend rows.
		self::assertEqualsWithDelta(1.0, $result->correlationMatrix[0][1], 0.05);
		self::assertEqualsWithDelta(1.0, $result->correlationMatrix[1][0], 0.05);
	}

	/**
	 * Real-world regression test: feed in 1 year of actual daily closes for the
	 * "Magnificent 6" (AAPL/MSFT/GOOGL/AMZN/NVDA/META) plus Bitcoin pulled from
	 * the production database, and verify that the correlation matrix lands in
	 * the empirically expected range. Locks in the alignment fix so we never
	 * regress to ~0 correlations for portfolios mixing trading calendars.
	 *
	 * Expected: average pairwise daily correlation for the 6 big-tech names sits
	 * around 0.30 (concentrated but typical noise dilution on daily returns); BTC
	 * is loosely correlated with tech (around 0-0.25 per pair) on daily horizon.
	 */
	public function testCorrelationMatrixMatchesRealMarketDataForBigTechAndBitcoin(): void
	{
		$priceData = $this->loadBigTechPriceFixture();

		$tickers = [];
		$assets = [];
		foreach (array_keys($priceData) as $index => $symbol) {
			$tickers[$symbol] = TickerFixture::getTicker(id: $index + 1, ticker: $symbol);
			$assets[] = AssetFixture::getAsset(id: $index + 1, ticker: $tickers[$symbol]);
		}

		$from = new DateTimeImmutable('2025-05-01');
		$to = new DateTimeImmutable('2026-05-01');

		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: $from),
		);
		$this->portfolioDataProvider->method('getPortfolioData')->willReturn(
			$this->makeCalculatedData(value: 100.0),
		);
		$this->assetProvider->method('getAssets')->willReturnCallback(
			static fn() => new ArrayIterator($assets),
		);
		$this->assetDataProvider->method('getAssetData')->willReturn(
			$this->makeAssetDataDto(units: new Decimal('10'), value: new Decimal('1000')),
		);

		// Return per-ticker prices: each entry is a list of TickerDataAdjustedDto.
		$tickerData = [];
		foreach ($priceData as $symbol => $rows) {
			$dtos = [];
			foreach ($rows as $date => $close) {
				$dtos[] = $this->makeAdjustedTickerData(
					new DateTimeImmutable($date),
					new Decimal((string) $close),
				);
			}
			$tickerData[$symbol] = $dtos;
		}

		$this->tickerDataProvider->method('getAdjustedTickerDatas')->willReturnCallback(
			static fn(Ticker $ticker) => $tickerData[$ticker->ticker] ?? [],
		);

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: RangeEnum::Custom,
			benchmarkTicker: null,
			customRangeFrom: $from,
			customRangeTo: $to,
		);

		$labels = $result->correlationLabels;
		$matrix = $result->correlationMatrix;

		self::assertCount(7, $labels);
		$bySymbol = array_flip($labels);

		// Diagonal is always 1.
		for ($i = 0; $i < 7; $i++) {
			self::assertEqualsWithDelta(1.0, $matrix[$i][$i], 0.0001, sprintf('Diagonal at %s', $labels[$i]));
		}

		// Spot-check known empirical pairs (values verified against the production DB on 2026-05-12).
		self::assertEqualsWithDelta(0.33, $matrix[$bySymbol['AAPL']][$bySymbol['AMZN']], 0.05);
		self::assertEqualsWithDelta(0.41, $matrix[$bySymbol['MSFT']][$bySymbol['NVDA']], 0.05);
		self::assertEqualsWithDelta(0.47, $matrix[$bySymbol['AMZN']][$bySymbol['META']], 0.05);
		// BTC vs tech is much weaker on daily horizon.
		self::assertLessThan(0.30, $matrix[$bySymbol['BTC']][$bySymbol['AAPL']]);
		self::assertLessThan(0.30, $matrix[$bySymbol['BTC']][$bySymbol['MSFT']]);

		// Average off-diagonal of upper triangle = diversification "score".
		$sum = 0.0;
		$count = 0;
		for ($i = 0; $i < 7; $i++) {
			for ($j = $i + 1; $j < 7; $j++) {
				$sum += $matrix[$i][$j];
				$count++;
			}
		}
		$avg = $sum / $count;
		// All-tech + BTC sits around 0.25 — concentrated but not extreme.
		self::assertEqualsWithDelta(0.25, $avg, 0.03);
	}

	/** @return array<string, array{RangeEnum}> */
	public static function rangesDataProvider(): array
	{
		return [
			'ThreeMonths' => [RangeEnum::ThreeMonths],
			'SixMonths' => [RangeEnum::SixMonths],
			'OneYear' => [RangeEnum::OneYear],
		];
	}

	#[DataProvider('rangesDataProvider')]
	public function testCalculateWithVariousRangesReturnsRiskDataDto(RangeEnum $range): void
	{
		$this->transactionProvider->method('getFirstTransaction')->willReturn(
			TransactionFixture::getTransaction(actionCreated: new DateTimeImmutable('2023-01-01')),
		);
		$this->portfolioDataProvider->method('getPortfolioData')->willReturn(
			$this->makeCalculatedData(value: 100.0),
		);
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$result = $this->calculator->calculate(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			range: $range,
			benchmarkTicker: null,
			customRangeFrom: null,
			customRangeTo: null,
		);

		// No assets configured → empty correlation matrix, zero metrics.
		self::assertSame([], $result->correlationLabels);
		self::assertSame(0.0, $result->beta);
	}

	private function makeCalculatedData(float $value = 0.0, float $returnPercentagePerAnnum = 0.0): CalculatedDataDto
	{
		$zero = new Decimal(0);

		return new CalculatedDataDto(
			date: new DateTimeImmutable(),
			value: new Decimal((string) $value),
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
			returnPercentagePerAnnum: $returnPercentagePerAnnum,
			tax: $zero,
			fee: $zero,
		);
	}

	private function makeAssetDataDto(Decimal $units, Decimal $value): AssetDataDto
	{
		$zero = new Decimal(0);

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

	private function makeAdjustedTickerData(DateTimeImmutable $date, Decimal $close): TickerDataAdjustedDto
	{
		$zero = new Decimal(0);

		return new TickerDataAdjustedDto(
			id: 1,
			ticker: TickerFixture::getTicker(),
			date: $date,
			open: $close,
			close: $close,
			high: $close,
			low: $close,
			volume: $zero,
		);
	}

	/** @return array<string, array<string, float|int>> */
	private function loadBigTechPriceFixture(): array
	{
		$raw = json_decode(
			(string) file_get_contents(__DIR__ . '/../../Fixtures/big_tech_prices_2025.json'),
			associative: true,
		);
		self::assertIsArray($raw);

		$result = [];
		foreach ($raw as $symbol => $rows) {
			self::assertIsString($symbol);
			self::assertIsArray($rows);
			$series = [];
			foreach ($rows as $date => $close) {
				self::assertIsString($date);
				self::assertTrue(is_int($close) || is_float($close));
				$series[$date] = $close;
			}
			$result[$symbol] = $series;
		}

		return $result;
	}
}
