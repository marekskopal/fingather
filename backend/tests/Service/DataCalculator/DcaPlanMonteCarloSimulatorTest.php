<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Service\DataCalculator\DcaPlanMonteCarloSimulator;
use FinGather\Service\DataCalculator\Dto\SimulationResultDto;
use FinGather\Service\DataCalculator\Dto\TickerWeightDto;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\TickerDataFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

#[CoversClass(DcaPlanMonteCarloSimulator::class)]
#[UsesClass(SimulationResultDto::class)]
#[UsesClass(TickerWeightDto::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(TickerData::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Sector::class)]
final class DcaPlanMonteCarloSimulatorTest extends TestCase
{
	public function testSimulateProducesOrderedPercentilesPerMonth(): void
	{
		$simulator = new DcaPlanMonteCarloSimulator(self::createStub(TickerDataProviderInterface::class));

		$result = $simulator->simulate(
			monthlyReturns: [0.97, 0.99, 1.00, 1.02, 1.05],
			startValue: 0.0,
			amount: 100.0,
			months: 12,
			simulations: 1000,
			randomizer: new Randomizer(new Mt19937(42)),
		);

		self::assertCount(12, $result->p10);
		self::assertCount(12, $result->p50);
		self::assertCount(12, $result->p90);

		for ($m = 0; $m < 12; $m++) {
			self::assertLessThanOrEqual($result->p50[$m], $result->p10[$m], 'p10 <= p50 at month ' . $m);
			self::assertLessThanOrEqual($result->p90[$m], $result->p50[$m], 'p50 <= p90 at month ' . $m);
		}
	}

	public function testSimulateWithZeroVolatilityCollapsesToDeterministic(): void
	{
		$simulator = new DcaPlanMonteCarloSimulator(self::createStub(TickerDataProviderInterface::class));

		// Constant 1.0 multiplier -> no growth, value(month n) = startValue + amount * n.
		$result = $simulator->simulate(
			monthlyReturns: [1.0],
			startValue: 1000.0,
			amount: 100.0,
			months: 6,
			simulations: 100,
			randomizer: new Randomizer(new Mt19937(7)),
		);

		for ($m = 0; $m < 6; $m++) {
			$expected = 1000.0 + 100.0 * ($m + 1);
			self::assertEqualsWithDelta($expected, $result->p10[$m], 1e-9, 'p10 deterministic at month ' . $m);
			self::assertEqualsWithDelta($expected, $result->p50[$m], 1e-9, 'p50 deterministic at month ' . $m);
			self::assertEqualsWithDelta($expected, $result->p90[$m], 1e-9, 'p90 deterministic at month ' . $m);
		}
	}

	public function testSimulateMedianTrendsAboveZeroVolatilityForPositiveReturns(): void
	{
		$simulator = new DcaPlanMonteCarloSimulator(self::createStub(TickerDataProviderInterface::class));

		// Symmetric set with mean > 1: median path should beat the no-growth deterministic baseline.
		$result = $simulator->simulate(
			monthlyReturns: [0.95, 1.00, 1.05, 1.10],
			startValue: 0.0,
			amount: 100.0,
			months: 24,
			simulations: 2000,
			randomizer: new Randomizer(new Mt19937(123)),
		);

		$flatBaseline = 100.0 * 24;
		self::assertGreaterThan($flatBaseline, $result->p50[23]);
	}

	public function testBuildMonthlyCompositeReturnsFromSingleTicker(): void
	{
		$ticker = TickerFixture::getTicker(id: 1);

		$rows = [
			TickerDataFixture::getTickerData(ticker: $ticker, date: new DateTimeImmutable('2025-04-25'), close: new Decimal('110')),
			TickerDataFixture::getTickerData(ticker: $ticker, date: new DateTimeImmutable('2025-03-25'), close: new Decimal('100')),
			TickerDataFixture::getTickerData(ticker: $ticker, date: new DateTimeImmutable('2025-02-25'), close: new Decimal('90')),
		];

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getTickerDatasByTickerId')->willReturn(new ArrayIterator($rows));

		$simulator = new DcaPlanMonteCarloSimulator($tickerDataProvider);

		$returns = $simulator->buildMonthlyCompositeReturns(
			tickerWeights: [new TickerWeightDto(tickerId: 1, weight: 1.0)],
			toDate: new DateTimeImmutable('2025-04-30'),
			historyYears: 1,
		);

		self::assertCount(2, $returns);
		// Feb close 90 -> Mar close 100 = 100/90
		self::assertEqualsWithDelta(100.0 / 90.0, $returns[0], 1e-9);
		// Mar close 100 -> Apr close 110 = 110/100
		self::assertEqualsWithDelta(110.0 / 100.0, $returns[1], 1e-9);
	}

	public function testBuildMonthlyCompositeReturnsHandlesEmptyWeights(): void
	{
		$simulator = new DcaPlanMonteCarloSimulator(self::createStub(TickerDataProviderInterface::class));

		$returns = $simulator->buildMonthlyCompositeReturns(
			tickerWeights: [],
			toDate: new DateTimeImmutable('2025-04-30'),
			historyYears: 1,
		);

		self::assertSame([], $returns);
	}

	public function testScaleMonthlyReturnsToMeanShiftsArithmeticMean(): void
	{
		$simulator = new DcaPlanMonteCarloSimulator(self::createStub(TickerDataProviderInterface::class));

		// Mean of [0.98, 1.00, 1.02, 1.05] = 1.0125; rescaling to target 1.005 multiplies by ~0.9926.
		$returns = [0.98, 1.00, 1.02, 1.05];
		$scaled = $simulator->scaleMonthlyReturnsToMean($returns, 1.005);

		$mean = array_sum($scaled) / count($scaled);
		self::assertEqualsWithDelta(1.005, $mean, 1e-12);

		// Volatility shape preserved: every element scaled by the same factor.
		self::assertCount(count($returns), $scaled);
		$factor = $scaled[0] / $returns[0];
		foreach ($returns as $i => $r) {
			self::assertEqualsWithDelta($r * $factor, $scaled[$i], 1e-12);
		}
	}

	public function testScaleMonthlyReturnsToMeanReturnsInputWhenEmpty(): void
	{
		$simulator = new DcaPlanMonteCarloSimulator(self::createStub(TickerDataProviderInterface::class));

		self::assertSame([], $simulator->scaleMonthlyReturnsToMean([], 1.005));
	}

	public function testSimulateBlockBootstrapPicksConsecutiveReturns(): void
	{
		$simulator = new DcaPlanMonteCarloSimulator(self::createStub(TickerDataProviderInterface::class));

		// Returns are all-or-nothing: index 0 is +10%, the rest is +0%. With block size = 12 and
		// only 1 simulation, the chosen block is a 12-month run starting at one index (with circular
		// wrap), so the path's terminal value depends only on the start index: either it includes
		// the +10% month (×1.10) or it doesn't. Either way, at most one +10% month is applied.
		$result = $simulator->simulate(
			monthlyReturns: [1.10, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00],
			startValue: 0.0,
			amount: 100.0,
			months: 12,
			simulations: 1,
			randomizer: new Randomizer(new Mt19937(1)),
			blockSize: 12,
		);

		// Strict i.i.d. resampling could compound +10% several times in 12 months; full-block
		// bootstrap with a 12-long sample applies it exactly once.
		// Lower bound: 12 contributions with no growth = 1200. Upper bound: 12 contributions
		// with at most one +10% applied (≈ 1320 if it lands on month 1, less if later).
		$terminal = $result->p50[11];
		self::assertGreaterThanOrEqual(1200.0 - 1e-6, $terminal);
		self::assertLessThanOrEqual(1320.0 + 1e-6, $terminal);
	}

	public function testSimulateBlockSizeOneMatchesIidBootstrap(): void
	{
		$simulator = new DcaPlanMonteCarloSimulator(self::createStub(TickerDataProviderInterface::class));

		$args = [
			'monthlyReturns' => [0.97, 0.99, 1.00, 1.02, 1.05],
			'startValue' => 0.0,
			'amount' => 100.0,
			'months' => 12,
			'simulations' => 500,
		];

		$default = $simulator->simulate(...$args, randomizer: new Randomizer(new Mt19937(99)));
		$blockOne = $simulator->simulate(...$args, randomizer: new Randomizer(new Mt19937(99)), blockSize: 1);

		// blockSize = 1 is the default; same seed must produce identical paths.
		for ($m = 0; $m < 12; $m++) {
			self::assertEqualsWithDelta($default->p10[$m], $blockOne->p10[$m], 1e-12);
			self::assertEqualsWithDelta($default->p50[$m], $blockOne->p50[$m], 1e-12);
			self::assertEqualsWithDelta($default->p90[$m], $blockOne->p90[$m], 1e-12);
		}
	}

	public function testBuildMonthlyCompositeReturnsSplicesProxyBeforeTickerInception(): void
	{
		// Held ticker (id 1) only has data for 2024-01..2024-04. Proxy ticker (id 2) has the full
		// 2023-12..2024-04 window. With splicing, the composite should include the 2024-01 multiplier
		// from the proxy (which only the proxy can produce, since the held ticker has no 2023-12 close
		// to pair with) and use the held ticker for 2024-02..04.
		$tickerRows = [
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2024-04-30'), close: new Decimal('110')),
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2024-03-29'), close: new Decimal('100')),
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2024-02-29'), close: new Decimal('95')),
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2024-01-31'), close: new Decimal('90')),
		];
		$proxyRows = [
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2024-04-30'), close: new Decimal('250')),
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2024-03-29'), close: new Decimal('240')),
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2024-02-29'), close: new Decimal('230')),
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2024-01-31'), close: new Decimal('220')),
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2023-12-29'), close: new Decimal('200')),
		];

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getTickerDatasByTickerId')
			->willReturnCallback(fn (int $tickerId) => $tickerId === 1 ? new ArrayIterator($tickerRows) : new ArrayIterator($proxyRows));

		$simulator = new DcaPlanMonteCarloSimulator($tickerDataProvider);

		$returns = $simulator->buildMonthlyCompositeReturns(
			tickerWeights: [new TickerWeightDto(tickerId: 1, weight: 1.0)],
			toDate: new DateTimeImmutable('2024-04-30'),
			historyYears: 1,
			proxyTickerIdByTickerId: [1 => 2],
		);

		// 2024-01 (proxy fills, ticker has no 2023-12 close): 220/200 = 1.10
		// 2024-02 (ticker takes over): 95/90
		// 2024-03 (ticker): 100/95
		// 2024-04 (ticker): 110/100
		self::assertCount(4, $returns);
		self::assertEqualsWithDelta(220.0 / 200.0, $returns[0], 1e-9);
		self::assertEqualsWithDelta(95.0 / 90.0, $returns[1], 1e-9);
		self::assertEqualsWithDelta(100.0 / 95.0, $returns[2], 1e-9);
		self::assertEqualsWithDelta(110.0 / 100.0, $returns[3], 1e-9);
	}

	public function testBuildMonthlyCompositeReturnsWithoutProxyMatchesTickerOnly(): void
	{
		// Ticker has 3 closes; without a proxy, the composite covers only the 2 within-ticker
		// monthly returns. Reference behavior — confirms the new code path with empty proxy map
		// is identical to the no-proxy case.
		$tickerRows = [
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-04-30'), close: new Decimal('110')),
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-03-31'), close: new Decimal('100')),
			TickerDataFixture::getTickerData(date: new DateTimeImmutable('2025-02-28'), close: new Decimal('90')),
		];

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getTickerDatasByTickerId')->willReturn(new ArrayIterator($tickerRows));

		$simulator = new DcaPlanMonteCarloSimulator($tickerDataProvider);

		$returns = $simulator->buildMonthlyCompositeReturns(
			tickerWeights: [new TickerWeightDto(tickerId: 1, weight: 1.0)],
			toDate: new DateTimeImmutable('2025-04-30'),
			historyYears: 1,
			proxyTickerIdByTickerId: [],
		);

		self::assertCount(2, $returns);
		self::assertEqualsWithDelta(100.0 / 90.0, $returns[0], 1e-9);
		self::assertEqualsWithDelta(110.0 / 100.0, $returns[1], 1e-9);
	}
}
