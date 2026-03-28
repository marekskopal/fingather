<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\PortfolioCashFlowDto;
use FinGather\Service\DataCalculator\MwrCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MwrCalculator::class)]
#[UsesClass(PortfolioCashFlowDto::class)]
final class MwrCalculatorTest extends TestCase
{
	private MwrCalculator $calculator;

	protected function setUp(): void
	{
		$this->calculator = new MwrCalculator();
	}

	public function testEmptyCashFlowsReturnsZero(): void
	{
		$result = $this->calculator->calculate(
			cashFlows: [],
			endingValue: new Decimal(1000),
			endDate: new DateTimeImmutable('2024-01-01'),
		);

		self::assertSame(0.0, $result);
	}

	public function testSingleYearInvestmentWithTenPercentReturn(): void
	{
		// Invest $1000 at t=0, portfolio worth $1100 after exactly 1 year.
		// MWR should converge to ~10% p.a.
		$date0 = new DateTimeImmutable('2023-01-01');
		$date1 = new DateTimeImmutable('2024-01-01');

		$cashFlows = [
			new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(1000)),
		];

		$result = $this->calculator->calculate(
			cashFlows: $cashFlows,
			endingValue: new Decimal(1100),
			endDate: $date1,
		);

		// 1100 = 1000 × (1 + r)^1  →  r = 10%
		self::assertEqualsWithDelta(10.0, $result, 0.01);
	}

	public function testTwoYearInvestmentWithKnownReturn(): void
	{
		// Invest $1000 at t=0, portfolio worth $1210 after 2 years.
		// MWR should be ~10% p.a. (compound).
		$date0 = new DateTimeImmutable('2022-01-01');
		$date2 = new DateTimeImmutable('2024-01-01');

		$cashFlows = [
			new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(1000)),
		];

		$result = $this->calculator->calculate(
			cashFlows: $cashFlows,
			endingValue: new Decimal(1210),
			endDate: $date2,
		);

		// 1210 = 1000 × (1+r)^2  →  r = 10%
		self::assertEqualsWithDelta(10.0, $result, 0.01);
	}

	public function testGoodTimingBoostsMwr(): void
	{
		// Investor deposits more money when the market is about to rise.
		// Date 0: invest $1000; portfolio grows 5% in 6 months.
		// Date 180 (6m): invest $5000 more; portfolio grows another 20% over next 6 months.
		// Ending value: ($1000 × 1.05 + $5000) × 1.20 = $7260

		$date0 = new DateTimeImmutable('2024-01-01');
		$date180 = new DateTimeImmutable('2024-06-29'); // ~180 days
		$dateEnd = new DateTimeImmutable('2024-12-25'); // ~360 days

		$cashFlows = [
			new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(1000)),
			new PortfolioCashFlowDto(date: $date180, netCashFlow: new Decimal(5000)),
		];

		$endingValue = new Decimal(7260);

		$result = $this->calculator->calculate(
			cashFlows: $cashFlows,
			endingValue: $endingValue,
			endDate: $dateEnd,
		);

		// MWR > TWR because of good timing — expect substantially positive return
		self::assertGreaterThan(10.0, $result);
	}

	public function testBadTimingReducesMwrVsTwr(): void
	{
		// Investor deposits a large amount just before a downturn.
		// Date 0: invest $1000; portfolio grows 20% over 6 months → $1200.
		// Date 180: invest $5000 more (at the peak); portfolio drops 5% over next 6 months.
		// Ending value: ($1200 + $5000) × 0.95 = $5890

		$date0 = new DateTimeImmutable('2024-01-01');
		$date180 = new DateTimeImmutable('2024-06-29');
		$dateEnd = new DateTimeImmutable('2024-12-25');

		$cashFlows = [
			new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(1000)),
			new PortfolioCashFlowDto(date: $date180, netCashFlow: new Decimal(5000)),
		];

		$endingValue = new Decimal(5890);

		$result = $this->calculator->calculate(
			cashFlows: $cashFlows,
			endingValue: $endingValue,
			endDate: $dateEnd,
		);

		// Invested $6000 total, portfolio is worth $5890 — MWR should be negative
		self::assertLessThan(0.0, $result);
	}
}
