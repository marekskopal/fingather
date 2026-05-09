<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator\LotMatcher;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\FifoMatchDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\DataCalculator\LotMatcher\AverageCostLotMatcher;
use FinGather\Service\Provider\Dto\SplitDto;
use FinGather\Tests\Fixtures\Model\Entity\SplitDtoFixture;
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AverageCostLotMatcher::class)]
#[UsesClass(FifoMatchDto::class)]
#[UsesClass(TransactionBuyDto::class)]
#[UsesClass(SplitDto::class)]
#[UsesClass(CalculatorUtils::class)]
final class AverageCostLotMatcherTest extends TestCase
{
	public function testCollapsesIntoSingleSyntheticAverageLot(): void
	{
		// 10 @ 100 + 10 @ 200 → avg 150
		$buys = [
			$this->buy(brokerId: 1, date: '2024-01-01', units: '10', price: '100'),
			$this->buy(brokerId: 1, date: '2024-06-01', units: '10', price: '200'),
		];

		$matcher = new AverageCostLotMatcher();
		$matches = $matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-12-01'),
			sellUnitsAbs: new Decimal('10'),
			splits: [],
		);

		self::assertCount(1, $matches);
		self::assertSame(150.0, $matches[0]->buy->priceDefaultCurrency->toFloat());
		// Synthetic actionCreated should be the earliest open buy date
		self::assertSame('2024-01-01', $matches[0]->buy->actionCreated->format('Y-m-d'));
		self::assertSame(10.0, $matches[0]->usedUnitsWithSplits->toFloat());
	}

	public function testProportionalConsumptionPreservesAveragePrice(): void
	{
		$buys = [
			$this->buy(brokerId: 1, date: '2024-01-01', units: '10', price: '100'),
			$this->buy(brokerId: 1, date: '2024-06-01', units: '10', price: '200'),
		];

		$matcher = new AverageCostLotMatcher();
		$matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-12-01'),
			sellUnitsAbs: new Decimal('10'),
			splits: [],
		);

		// Half consumed → both lots still present at half their original units
		self::assertCount(2, $buys);
		$remainingValues = array_values($buys);
		self::assertSame(5.0, $remainingValues[0]->units->toFloat());
		self::assertSame(5.0, $remainingValues[1]->units->toFloat());
	}

	public function testFullyConsumesAllLotsWhenSellMatchesTotal(): void
	{
		$buys = [
			$this->buy(brokerId: 1, date: '2024-01-01', units: '10', price: '100'),
			$this->buy(brokerId: 1, date: '2024-06-01', units: '10', price: '200'),
		];

		$matcher = new AverageCostLotMatcher();
		$matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-12-01'),
			sellUnitsAbs: new Decimal('20'),
			splits: [],
		);

		self::assertCount(0, $buys);
	}

	public function testRespectsBrokerScope(): void
	{
		$buys = [
			$this->buy(brokerId: 1, date: '2024-01-01', units: '10', price: '100'),
			$this->buy(brokerId: 2, date: '2024-06-01', units: '10', price: '999'),
		];

		$matcher = new AverageCostLotMatcher();
		$matches = $matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-12-01'),
			sellUnitsAbs: new Decimal('10'),
			splits: [],
		);

		self::assertCount(1, $matches);
		self::assertSame(100.0, $matches[0]->buy->priceDefaultCurrency->toFloat());
		// Broker 2 lot untouched
		self::assertCount(1, $buys);
		self::assertSame(2, array_values($buys)[0]->brokerId);
	}

	public function testNoOpenLotsReturnsEmpty(): void
	{
		$buys = [];

		$matcher = new AverageCostLotMatcher();
		$matches = $matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-12-01'),
			sellUnitsAbs: new Decimal('10'),
			splits: [],
		);

		self::assertSame([], $matches);
	}

	public function testWeightedAverageAcrossStockSplit(): void
	{
		// Buy 5 @ 100 (cost = 500), buy 5 @ 200 (cost = 1000), 2:1 split affects both.
		// Total split-adjusted units = 5*2 + 5*2 = 20. Total cost = 1500.
		// Avg = 75 per split-adjusted unit. Sell 10 split-adjusted (half) → costBasis = 750.
		$buys = [
			$this->buy(brokerId: 1, date: '2024-01-01', units: '5', price: '100'),
			$this->buy(brokerId: 1, date: '2024-04-01', units: '5', price: '200'),
		];

		$splits = [
			SplitDtoFixture::getSplitDto(
				date: new DateTimeImmutable('2024-05-01'),
				factor: new Decimal(2),
			),
		];

		$matcher = new AverageCostLotMatcher();
		$matches = $matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-06-01'),
			sellUnitsAbs: new Decimal('10'),
			splits: $splits,
		);

		self::assertCount(1, $matches);
		// Synthetic average price per split-adjusted unit = 1500 / 20 = 75.
		self::assertSame(75.0, $matches[0]->buy->priceDefaultCurrency->toFloat());
		// Earliest open buy date wins for holding-period bookkeeping.
		self::assertSame('2024-01-01', $matches[0]->buy->actionCreated->format('Y-m-d'));
		self::assertSame(10.0, $matches[0]->usedUnitsWithSplits->toFloat());

		// Half consumed proportionally: each lot's original units halved (5 → 2.5).
		self::assertCount(2, $buys);
		$remainingValues = array_values($buys);
		self::assertSame(2.5, $remainingValues[0]->units->toFloat());
		self::assertSame(2.5, $remainingValues[1]->units->toFloat());
	}

	private function buy(int $brokerId, string $date, string $units, string $price): TransactionBuyDto
	{
		return new TransactionBuyDto(
			brokerId: $brokerId,
			actionCreated: new DateTimeImmutable($date),
			units: new Decimal($units),
			priceTickerCurrency: new Decimal($price),
			priceDefaultCurrency: new Decimal($price),
			priceWithSplitTickerCurrency: new Decimal($price),
			priceWithSplitDefaultCurrency: new Decimal($price),
		);
	}
}
