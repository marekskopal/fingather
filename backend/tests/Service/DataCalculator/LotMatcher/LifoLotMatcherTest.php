<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator\LotMatcher;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\FifoMatchDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\DataCalculator\LotMatcher\LifoLotMatcher;
use FinGather\Service\Provider\Dto\SplitDto;
use FinGather\Tests\Fixtures\Model\Entity\SplitDtoFixture;
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LifoLotMatcher::class)]
#[UsesClass(FifoMatchDto::class)]
#[UsesClass(TransactionBuyDto::class)]
#[UsesClass(SplitDto::class)]
#[UsesClass(CalculatorUtils::class)]
final class LifoLotMatcherTest extends TestCase
{
	public function testConsumesNewestLotFirst(): void
	{
		$buys = [
			$this->buy(brokerId: 1, date: '2024-01-01', units: '10', price: '100'),
			$this->buy(brokerId: 1, date: '2024-06-01', units: '10', price: '150'),
		];

		$matcher = new LifoLotMatcher();
		$matches = $matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-12-01'),
			sellUnitsAbs: new Decimal('10'),
			splits: [],
		);

		self::assertCount(1, $matches);
		self::assertSame(150.0, $matches[0]->buy->priceDefaultCurrency->toFloat());
		self::assertSame('2024-06-01', $matches[0]->buy->actionCreated->format('Y-m-d'));
		// The 2024-01-01 lot should still be in $buys
		self::assertCount(1, $buys);
	}

	public function testConsumesAcrossMultipleLotsWhenNeeded(): void
	{
		$buys = [
			$this->buy(brokerId: 1, date: '2024-01-01', units: '10', price: '100'),
			$this->buy(brokerId: 1, date: '2024-06-01', units: '10', price: '150'),
		];

		$matcher = new LifoLotMatcher();
		$matches = $matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-12-01'),
			sellUnitsAbs: new Decimal('15'),
			splits: [],
		);

		self::assertCount(2, $matches);
		// Newest first
		self::assertSame(150.0, $matches[0]->buy->priceDefaultCurrency->toFloat());
		self::assertSame(10.0, $matches[0]->usedUnitsWithSplits->toFloat());
		// Then older lot, partial
		self::assertSame(100.0, $matches[1]->buy->priceDefaultCurrency->toFloat());
		self::assertSame(5.0, $matches[1]->usedUnitsWithSplits->toFloat());

		// 5 units of the 2024-01-01 lot should remain
		self::assertCount(1, $buys);
		$remaining = array_values($buys)[0];
		self::assertSame(5.0, $remaining->units->toFloat());
	}

	public function testRespectsBrokerScope(): void
	{
		$buys = [
			$this->buy(brokerId: 1, date: '2024-01-01', units: '10', price: '100'),
			$this->buy(brokerId: 2, date: '2024-06-01', units: '10', price: '999'),
		];

		$matcher = new LifoLotMatcher();
		$matches = $matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-12-01'),
			sellUnitsAbs: new Decimal('10'),
			splits: [],
		);

		self::assertCount(1, $matches);
		self::assertSame(100.0, $matches[0]->buy->priceDefaultCurrency->toFloat());
		// Broker 2 lot must remain untouched
		self::assertCount(1, $buys);
		self::assertSame(2, array_values($buys)[0]->brokerId);
	}

	public function testRespectsStockSplitOnNewerLot(): void
	{
		// Buy 5 @ 100 (older), buy 5 @ 200 (newer), 2:1 split between newer-buy and sell.
		// LIFO consumes the newer lot first; its 5 original units = 10 split-adjusted units.
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

		$matcher = new LifoLotMatcher();
		$matches = $matcher->consumeLots(
			$buys,
			brokerId: 1,
			sellDate: new DateTimeImmutable('2024-06-01'),
			sellUnitsAbs: new Decimal('5'),
			splits: $splits,
		);

		self::assertCount(1, $matches);
		// Newer lot ($200) consumed; usedOriginalUnits = 5/2 = 2.5.
		self::assertSame(200.0, $matches[0]->buy->priceDefaultCurrency->toFloat());
		self::assertSame(5.0, $matches[0]->usedUnitsWithSplits->toFloat());
		self::assertSame(2.5, $matches[0]->usedOriginalUnits->toFloat());

		// Newer lot should still be open with 2.5 original units (= 5 split-adjusted).
		self::assertCount(2, $buys);
		$newerLot = array_values($buys)[1];
		self::assertSame('2024-04-01', $newerLot->actionCreated->format('Y-m-d'));
		self::assertSame(2.5, $newerLot->units->toFloat());
		// Older lot untouched.
		self::assertSame(5.0, array_values($buys)[0]->units->toFloat());
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
