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
}
