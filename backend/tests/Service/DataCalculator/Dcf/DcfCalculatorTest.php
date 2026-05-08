<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator\Dcf;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dcf\DcfCalculationException;
use FinGather\Service\DataCalculator\Dcf\DcfCalculator;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfAssumptions;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfHistoryPointDto;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfInputs;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfResult;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfValuationStatusEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DcfCalculator::class)]
#[UsesClass(DcfAssumptions::class)]
#[UsesClass(DcfHistoryPointDto::class)]
#[UsesClass(DcfInputs::class)]
#[UsesClass(DcfResult::class)]
final class DcfCalculatorTest extends TestCase
{
	private DcfCalculator $calculator;

	protected function setUp(): void
	{
		$this->calculator = new DcfCalculator();
	}

	public function testApplLandsNearAlphaSpread(): void
	{
		$inputs = new DcfInputs(
			sharesOutstanding: 14687356000,
			latestRevenue: 416161000000,
			latestFcfe: 101090746368,
			quarterlyRevenueGrowth: 0.166,
			beta: 1.27,
			history: [
				new DcfHistoryPointDto(new DateTimeImmutable('2025-09-30'), 98767000000, 416161000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2024-09-30'), 108807000000, 391035000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2023-09-30'), 99584000000, 383285000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2022-09-30'), 111443000000, 394328000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2021-09-30'), 92953000000, 365817000000),
			],
			currentPrice: null,
		);

		$result = $this->calculator->calculate($inputs, DcfAssumptions::default());

		// AlphaSpread base case = $173. We expect to land in the same neighborhood.
		$intrinsic = (float) (string) $result->intrinsicValuePerShare;
		self::assertGreaterThan(150.0, $intrinsic);
		self::assertLessThan(195.0, $intrinsic);
		self::assertEqualsWithDelta(0.0993, $result->appliedGrowthRate, 0.005);
		self::assertEqualsWithDelta(0.262, $result->appliedFcfMargin, 0.005);
		self::assertCount(5, $result->projectedRevenues);
	}

	public function testGrowthRateClampsToMax(): void
	{
		// NVDA-like blowout quarterly growth (73%) should be clamped to 30% by default.
		$inputs = new DcfInputs(
			sharesOutstanding: 24304000000,
			latestRevenue: 215938000000,
			latestFcfe: 58128998400,
			quarterlyRevenueGrowth: 0.732,
			beta: 1.5,
			history: [
				new DcfHistoryPointDto(new DateTimeImmutable('2025-01-31'), 60000000000, 215000000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2024-01-31'), 30000000000, 130000000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2023-01-31'), 5000000000, 60000000000),
			],
			currentPrice: null,
		);

		$result = $this->calculator->calculate($inputs, DcfAssumptions::default());

		self::assertSame(0.30, $result->appliedGrowthRate);
	}

	public function testGrowthRateOverrideIsApplied(): void
	{
		$inputs = new DcfInputs(
			sharesOutstanding: 1000000000,
			latestRevenue: 100000000000,
			latestFcfe: 20000000000,
			quarterlyRevenueGrowth: 0.05,
			beta: 1.0,
			history: [
				new DcfHistoryPointDto(new DateTimeImmutable('2025-01-01'), 20000000000, 100000000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2024-01-01'), 18000000000, 95000000000),
			],
			currentPrice: null,
		);

		$result = $this->calculator->calculate($inputs, DcfAssumptions::default()->with(growthRateOverride: 0.07));

		self::assertSame(0.07, $result->appliedGrowthRate);
	}

	public function testMissingGrowthSignalsThrows(): void
	{
		$inputs = new DcfInputs(
			sharesOutstanding: 1000000000,
			latestRevenue: 100000000000,
			latestFcfe: 20000000000,
			quarterlyRevenueGrowth: null,
			beta: null,
			history: [
				new DcfHistoryPointDto(new DateTimeImmutable('2025-01-01'), 20000000000, 100000000000),
			],
			currentPrice: null,
		);

		$this->expectException(DcfCalculationException::class);
		$this->calculator->calculate($inputs, DcfAssumptions::default());
	}

	public function testZeroSharesOutstandingThrows(): void
	{
		$inputs = new DcfInputs(
			sharesOutstanding: 0,
			latestRevenue: 100,
			latestFcfe: 10,
			quarterlyRevenueGrowth: 0.05,
			beta: null,
			history: [],
			currentPrice: null,
		);

		$this->expectException(DcfCalculationException::class);
		$this->calculator->calculate($inputs, DcfAssumptions::default());
	}

	public function testFcfMarginFallsBackToTtmRatio(): void
	{
		// History has revenue but no FCF rows; calculator should fall back to TTM ratio.
		$inputs = new DcfInputs(
			sharesOutstanding: 1000000000,
			latestRevenue: 100000000000,
			latestFcfe: 25000000000,
			quarterlyRevenueGrowth: 0.05,
			beta: null,
			history: [
				new DcfHistoryPointDto(new DateTimeImmutable('2025-01-01'), null, 100000000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2024-01-01'), null, 95000000000),
			],
			currentPrice: null,
		);

		$result = $this->calculator->calculate($inputs, DcfAssumptions::default());

		self::assertEqualsWithDelta(0.25, $result->appliedFcfMargin, 0.001);
	}

	public function testValuationOvervaluedMatchesAlphaSpreadFormula(): void
	{
		// AAPL-like inputs with current market price ~$287.
		// Intrinsic ≈ $174.35; AlphaSpread reports "Overvalued by ~40%".
		// Formula: (price − intrinsic) / price = (287 − 174.35) / 287 = 39.25%.
		$inputs = $this->aaplInputs(currentPrice: new Decimal('287'));

		$result = $this->calculator->calculate($inputs, DcfAssumptions::default());

		self::assertNotNull($result->valuationDiffPercent);
		self::assertEqualsWithDelta(39.25, $result->valuationDiffPercent, 0.5);
		self::assertSame(DcfValuationStatusEnum::Overvalued, $result->valuationStatus);
		self::assertNotNull($result->currentPrice);
		self::assertSame('287', (string) $result->currentPrice);
	}

	public function testValuationUndervaluedReturnsNegativeDiff(): void
	{
		// Same AAPL inputs (intrinsic ≈ $174.35), but suppose price collapsed to $100.
		// (100 − 174.35) / 100 = −74.35% → undervalued, diff is negative.
		$inputs = $this->aaplInputs(currentPrice: new Decimal('100'));

		$result = $this->calculator->calculate($inputs, DcfAssumptions::default());

		self::assertNotNull($result->valuationDiffPercent);
		self::assertLessThan(0.0, $result->valuationDiffPercent);
		self::assertSame(DcfValuationStatusEnum::Undervalued, $result->valuationStatus);
	}

	public function testValuationFairWithinFivePercentBand(): void
	{
		// Price = intrinsic ($174.35) → diff 0% → Fair. Try a few prices inside the ±5% band.
		$inputs = $this->aaplInputs(currentPrice: new Decimal('174'));

		$result = $this->calculator->calculate($inputs, DcfAssumptions::default());

		self::assertSame(DcfValuationStatusEnum::FairlyValued, $result->valuationStatus);
	}

	public function testValuationStatusNullWhenCurrentPriceMissing(): void
	{
		$inputs = $this->aaplInputs(currentPrice: null);

		$result = $this->calculator->calculate($inputs, DcfAssumptions::default());

		self::assertNull($result->valuationDiffPercent);
		self::assertNull($result->valuationStatus);
		self::assertNull($result->currentPrice);
	}

	private function aaplInputs(?Decimal $currentPrice): DcfInputs
	{
		return new DcfInputs(
			sharesOutstanding: 14687356000,
			latestRevenue: 416161000000,
			latestFcfe: 101090746368,
			quarterlyRevenueGrowth: 0.166,
			beta: 1.27,
			history: [
				new DcfHistoryPointDto(new DateTimeImmutable('2025-09-30'), 98767000000, 416161000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2024-09-30'), 108807000000, 391035000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2023-09-30'), 99584000000, 383285000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2022-09-30'), 111443000000, 394328000000),
				new DcfHistoryPointDto(new DateTimeImmutable('2021-09-30'), 92953000000, 365817000000),
			],
			currentPrice: $currentPrice,
		);
	}
}
