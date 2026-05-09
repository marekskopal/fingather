<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\PortfolioCashFlowDto;
use FinGather\Service\DataCalculator\TwrCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TwrCalculator::class)]
#[UsesClass(PortfolioCashFlowDto::class)]
final class TwrCalculatorTest extends TestCase
{
	private TwrCalculator $calculator;

	protected function setUp(): void
	{
		$this->calculator = new TwrCalculator();
	}

	public function testEmptyCashFlowsReturnsZero(): void
	{
		$result = $this->calculator->calculate(
			cashFlows: [],
			portfolioValueFetcher: static fn (): Decimal => new Decimal(0),
			currentValue: new Decimal(1000),
			currentDate: new DateTimeImmutable('2024-01-01'),
		);

		self::assertSame(0.0, $result);
	}

	public function testSingleInvestmentNoGrowthReturnsZero(): void
	{
		// Investor buys $1000 on day 0, no further CFs, portfolio still $1000 today.
		$date0 = new DateTimeImmutable('2024-01-01');
		$cashFlows = [new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(1000))];

		$result = $this->calculator->calculate(
			cashFlows: $cashFlows,
			portfolioValueFetcher: static fn (): Decimal => new Decimal(1000),
			currentValue: new Decimal(1000),
			currentDate: $date0,
		);

		// Same date — no sub-period, TWR = 0%
		self::assertSame(0.0, $result);
	}

	public function testSingleInvestmentWithGrowth(): void
	{
		// Invest $1000 on day 0, portfolio grows to $1100 at calculation date.
		$date0 = new DateTimeImmutable('2024-01-01');
		$dateCalc = new DateTimeImmutable('2024-07-01');

		$cashFlows = [new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(1000))];

		$result = $this->calculator->calculate(
			cashFlows: $cashFlows,
			portfolioValueFetcher: static fn (): Decimal => new Decimal(1000),
			currentValue: new Decimal(1100),
			currentDate: $dateCalc,
		);

		// r = 1100/1000 - 1 = 10%
		self::assertSame(10.0, $result);
	}

	public function testTwoSubPeriodsWithBuy(): void
	{
		// Scenario:
		//   Date 0: Invest $1000 → portfolio = $1000
		//   Date 30: Portfolio grew to $1050 (before new CF), investor adds $500 → portfolio = $1550
		//   Date 60 (calc): Portfolio = $1700

		$date0 = new DateTimeImmutable('2024-01-01');
		$date30 = new DateTimeImmutable('2024-01-31');
		$date60 = new DateTimeImmutable('2024-03-01');

		$cashFlows = [
			new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(1000)),
			new PortfolioCashFlowDto(date: $date30, netCashFlow: new Decimal(500)),
		];

		// Portfolio values at each CF date (end of day, after CF)
		$portfolioValues = [
			$date0->format('Y-m-d') => new Decimal(1000),
			$date30->format('Y-m-d') => new Decimal(1550),
		];

		$result = $this->calculator->calculate(
			cashFlows: $cashFlows,
			portfolioValueFetcher: function (DateTimeImmutable $d) use ($portfolioValues): Decimal {
				return $portfolioValues[$d->format('Y-m-d')] ?? new Decimal(0);
			},
			currentValue: new Decimal(1700),
			currentDate: $date60,
		);

		// Sub-period 1: V_end_before_CF = 1550 - 500 = 1050. r1 = 1050/1000 - 1 = 5%
		// Sub-period 2: r2 = 1700/1550 - 1 ≈ 9.6774...%
		// TWR = (1.05 × 1.09677...) - 1 ≈ 15.16%
		self::assertSame(15.16, $result);
	}

	public function testSellTransactionSubPeriod(): void
	{
		// Scenario:
		//   Date 0: Invest $1000 → portfolio = $1000
		//   Date 30: Portfolio at $1200 (before sell), investor sells $200 worth → portfolio = $1000
		//   Date 60 (calc): Portfolio = $1100

		$date0 = new DateTimeImmutable('2024-01-01');
		$date30 = new DateTimeImmutable('2024-01-31');
		$date60 = new DateTimeImmutable('2024-03-01');

		$cashFlows = [
			new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(1000)),
			// Sell: units are negative → net CF is negative
			new PortfolioCashFlowDto(date: $date30, netCashFlow: new Decimal(-200)),
		];

		$portfolioValues = [
			$date0->format('Y-m-d') => new Decimal(1000),
			// 1200 - 200 = 1000 after sell
			$date30->format('Y-m-d') => new Decimal(1000),
		];

		$result = $this->calculator->calculate(
			cashFlows: $cashFlows,
			portfolioValueFetcher: function (DateTimeImmutable $d) use ($portfolioValues): Decimal {
				return $portfolioValues[$d->format('Y-m-d')] ?? new Decimal(0);
			},
			currentValue: new Decimal(1100),
			currentDate: $date60,
		);

		// Sub-period 1: V_end_before_CF = 1000 - (-200) = 1200. r1 = 1200/1000 - 1 = 20%
		// Sub-period 2: r2 = 1100/1000 - 1 = 10%
		// TWR = (1.20 × 1.10) - 1 = 32%
		self::assertSame(32.0, $result);
	}

	public function testAggregatesMultipleCashFlowsSameDay(): void
	{
		// Two buys on the same day should be summed into one sub-period boundary.
		$date0 = new DateTimeImmutable('2024-01-01');
		$date30 = new DateTimeImmutable('2024-01-31');
		$dateCalc = new DateTimeImmutable('2024-03-01');

		$cashFlows = [
			new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(600)),
			// same day → net $1000
			new PortfolioCashFlowDto(date: $date0, netCashFlow: new Decimal(400)),
			new PortfolioCashFlowDto(date: $date30, netCashFlow: new Decimal(500)),
		];

		$portfolioValues = [
			$date0->format('Y-m-d') => new Decimal(1000),
			$date30->format('Y-m-d') => new Decimal(1550),
		];

		$result = $this->calculator->calculate(
			cashFlows: $cashFlows,
			portfolioValueFetcher: function (DateTimeImmutable $d) use ($portfolioValues): Decimal {
				return $portfolioValues[$d->format('Y-m-d')] ?? new Decimal(0);
			},
			currentValue: new Decimal(1700),
			currentDate: $dateCalc,
		);

		// Same as testTwoSubPeriodsWithBuy — aggregation should not change result
		self::assertSame(15.16, $result);
	}
}
