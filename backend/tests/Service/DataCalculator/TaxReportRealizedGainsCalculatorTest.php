<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainsDto;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainTransactionDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\DataCalculator\TaxReportRealizedGainsCalculator;
use FinGather\Service\Provider\CurrentTransactionProvider;
use FinGather\Service\Provider\Dto\SplitDto;
use FinGather\Service\Provider\SplitProvider;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\SplitDtoFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxReportRealizedGainsCalculator::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(Transaction::class)]
#[UsesClass(User::class)]
#[UsesClass(TaxReportRealizedGainsDto::class)]
#[UsesClass(TaxReportRealizedGainTransactionDto::class)]
#[UsesClass(TransactionBuyDto::class)]
final class TaxReportRealizedGainsCalculatorTest extends TestCase
{
	private readonly User $user;

	private readonly Portfolio $portfolio;

	private readonly DateTimeImmutable $yearStart;

	private readonly DateTimeImmutable $yearEnd;

	protected function setUp(): void
	{
		$this->user = UserFixture::getUser();
		$this->portfolio = PortfolioFixture::getPortfolio();
		$this->yearStart = new DateTimeImmutable('2024-01-01');
		$this->yearEnd = new DateTimeImmutable('2024-12-31');
	}

	public function testCalculateSimpleGain(): void
	{
		// Buy 10 units @ 100, sell 10 units @ 150 → gain of 500
		$transactions = [
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Buy,
				actionCreated: new DateTimeImmutable('2024-01-01'),
				units: new Decimal(10),
				priceDefaultCurrency: new Decimal(100),
				feeDefaultCurrency: new Decimal(0),
			),
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Sell,
				actionCreated: new DateTimeImmutable('2024-06-15'),
				units: new Decimal(-10),
				priceDefaultCurrency: new Decimal(150),
				feeDefaultCurrency: new Decimal(5),
			),
		];

		$result = $this->calculate([$transactions]);

		// 10 * 150
		self::assertSame(1500.0, $result->totalSalesProceeds->toFloat());
		// 10 * 100
		self::assertSame(1000.0, $result->totalCostBasis->toFloat());
		self::assertSame(500.0, $result->totalGains->toFloat());
		self::assertSame(0.0, $result->totalLosses->toFloat());
		self::assertSame(5.0, $result->totalFees->toFloat());
		self::assertSame(500.0, $result->netRealizedGainLoss->toFloat());
		self::assertCount(1, $result->transactions);
	}

	public function testCalculateSimpleLoss(): void
	{
		// Buy 10 units @ 100, sell 10 units @ 80 → loss of 200
		$transactions = [
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Buy,
				actionCreated: new DateTimeImmutable('2024-01-01'),
				units: new Decimal(10),
				priceDefaultCurrency: new Decimal(100),
				feeDefaultCurrency: new Decimal(0),
			),
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Sell,
				actionCreated: new DateTimeImmutable('2024-06-15'),
				units: new Decimal(-10),
				priceDefaultCurrency: new Decimal(80),
				feeDefaultCurrency: new Decimal(0),
			),
		];

		$result = $this->calculate([$transactions]);

		// 10 * 80
		self::assertSame(800.0, $result->totalSalesProceeds->toFloat());
		// 10 * 100
		self::assertSame(1000.0, $result->totalCostBasis->toFloat());
		self::assertSame(0.0, $result->totalGains->toFloat());
		self::assertSame(200.0, $result->totalLosses->toFloat());
		self::assertSame(-200.0, $result->netRealizedGainLoss->toFloat());
		self::assertCount(1, $result->transactions);
	}

	public function testCalculateFifoConsumesLotsInOrder(): void
	{
		// Buy 5 units @ 100 (lot 1), buy 3 units @ 120 (lot 2), sell 8 units @ 130
		// FIFO: lot 1 consumed first (5 units), then lot 2 (3 units)
		$transactions = [
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Buy,
				actionCreated: new DateTimeImmutable('2024-01-01'),
				units: new Decimal(5),
				priceDefaultCurrency: new Decimal(100),
				feeDefaultCurrency: new Decimal(0),
			),
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Buy,
				actionCreated: new DateTimeImmutable('2024-02-01'),
				units: new Decimal(3),
				priceDefaultCurrency: new Decimal(120),
				feeDefaultCurrency: new Decimal(0),
			),
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Sell,
				actionCreated: new DateTimeImmutable('2024-06-15'),
				units: new Decimal(-8),
				priceDefaultCurrency: new Decimal(130),
				feeDefaultCurrency: new Decimal(0),
			),
		];

		$result = $this->calculate([$transactions]);

		// Lot 1: proceeds = 5*130=650, costBasis = 5*100=500, gain = 150
		// Lot 2: proceeds = 3*130=390, costBasis = 3*120=360, gain = 30
		// 650+390
		self::assertSame(1040.0, $result->totalSalesProceeds->toFloat());
		// 500+360
		self::assertSame(860.0, $result->totalCostBasis->toFloat());
		// 150+30
		self::assertSame(180.0, $result->totalGains->toFloat());
		self::assertSame(0.0, $result->totalLosses->toFloat());
		self::assertSame(180.0, $result->netRealizedGainLoss->toFloat());
		self::assertCount(2, $result->transactions);
	}

	public function testCalculateSellOutsideYearConsumesLotsWithoutReporting(): void
	{
		// Buy 10 units @ 100 before year start, sell 5 units outside year range
		// (consumes part of the lot without reporting), then sell remaining 5 within year
		$transactions = [
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Buy,
				actionCreated: new DateTimeImmutable('2023-01-01'),
				units: new Decimal(10),
				priceDefaultCurrency: new Decimal(100),
				feeDefaultCurrency: new Decimal(0),
			),
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Sell,
				// outside year range
				actionCreated: new DateTimeImmutable('2023-06-15'),
				units: new Decimal(-5),
				priceDefaultCurrency: new Decimal(150),
				feeDefaultCurrency: new Decimal(0),
			),
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Sell,
				// inside year range
				actionCreated: new DateTimeImmutable('2024-06-15'),
				units: new Decimal(-5),
				priceDefaultCurrency: new Decimal(130),
				feeDefaultCurrency: new Decimal(0),
			),
		];

		$result = $this->calculate([$transactions]);

		// Only 2024 sell is reported: 5 units @ 130 (remaining lot after 2023 sell consumed 5)
		// 5 * 130
		self::assertSame(650.0, $result->totalSalesProceeds->toFloat());
		// 5 * 100
		self::assertSame(500.0, $result->totalCostBasis->toFloat());
		self::assertSame(150.0, $result->totalGains->toFloat());
		self::assertSame(0.0, $result->totalLosses->toFloat());
		self::assertSame(150.0, $result->netRealizedGainLoss->toFloat());
		self::assertCount(1, $result->transactions);
	}

	public function testCalculateWithStockSplit(): void
	{
		// Buy 5 units @ 100 on 2024-01-01, 2:1 split on 2024-03-01, sell 10 units @ 55
		// Split factor between buy and sell = 2 → buyUnitsWithSplits = 5*2 = 10
		// sellProceeds = 10 * 55 = 550, costBasis = 5 * 100 = 500, gain = 50
		$transactions = [
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Buy,
				actionCreated: new DateTimeImmutable('2024-01-01'),
				units: new Decimal(5),
				priceDefaultCurrency: new Decimal(100),
				feeDefaultCurrency: new Decimal(0),
			),
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Sell,
				actionCreated: new DateTimeImmutable('2024-06-15'),
				units: new Decimal(-10),
				priceDefaultCurrency: new Decimal(55),
				feeDefaultCurrency: new Decimal(0),
			),
		];

		$splits = [
			SplitDtoFixture::getSplitDto(
				date: new DateTimeImmutable('2024-03-01'),
				factor: new Decimal(2),
			),
		];

		$result = $this->calculate([$transactions], $splits);

		// 10 * 55
		self::assertSame(550.0, $result->totalSalesProceeds->toFloat());
		// 5 * 100
		self::assertSame(500.0, $result->totalCostBasis->toFloat());
		self::assertSame(50.0, $result->totalGains->toFloat());
		self::assertSame(0.0, $result->totalLosses->toFloat());
		self::assertSame(50.0, $result->netRealizedGainLoss->toFloat());
		self::assertCount(1, $result->transactions);
	}

	public function testCalculateNoTransactionsReturnsZeros(): void
	{
		$result = $this->calculate([]);

		self::assertSame(0.0, $result->totalSalesProceeds->toFloat());
		self::assertSame(0.0, $result->totalCostBasis->toFloat());
		self::assertSame(0.0, $result->totalGains->toFloat());
		self::assertSame(0.0, $result->totalLosses->toFloat());
		self::assertSame(0.0, $result->totalFees->toFloat());
		self::assertSame(0.0, $result->netRealizedGainLoss->toFloat());
		self::assertCount(0, $result->transactions);
	}

	public function testCalculateTransactionDtoHasCorrectFields(): void
	{
		$buyDate = new DateTimeImmutable('2024-01-01');
		$sellDate = new DateTimeImmutable('2024-06-15');

		$transactions = [
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Buy,
				actionCreated: $buyDate,
				units: new Decimal(10),
				priceDefaultCurrency: new Decimal(100),
				feeDefaultCurrency: new Decimal(0),
			),
			TransactionFixture::getTransaction(
				actionType: TransactionActionTypeEnum::Sell,
				actionCreated: $sellDate,
				units: new Decimal(-10),
				priceDefaultCurrency: new Decimal(120),
				feeDefaultCurrency: new Decimal(3),
			),
		];

		$result = $this->calculate([$transactions]);

		self::assertCount(1, $result->transactions);

		$tx = $result->transactions[0];
		self::assertSame('AAPL', $tx->tickerTicker);
		self::assertSame('Apple Inc.', $tx->tickerName);
		self::assertSame('2024-01-01', $tx->buyDate);
		self::assertSame('2024-06-15', $tx->sellDate);
		// 2024-01-01 to 2024-06-15 (2024 is a leap year)
		self::assertSame(166, $tx->holdingPeriodDays);
		self::assertSame(10.0, $tx->units->toFloat());
		self::assertSame(100.0, $tx->buyPrice->toFloat());
		self::assertSame(120.0, $tx->sellPrice->toFloat());
		// 10 * 100
		self::assertSame(1000.0, $tx->costBasis->toFloat());
		// 10 * 120
		self::assertSame(1200.0, $tx->salesProceeds->toFloat());
		self::assertSame(3.0, $tx->fee->toFloat());
		self::assertSame(200.0, $tx->gainLoss->toFloat());
	}

	/**
	 * @param list<list<Transaction>> $transactionsByAsset
	 * @param list<SplitDto> $splits
	 */
	private function calculate(array $transactionsByAsset, array $splits = []): TaxReportRealizedGainsDto
	{
		$currentTransactionProvider = self::createStub(CurrentTransactionProvider::class);
		$currentTransactionProvider->method('loadTransactions')
			->willReturn($transactionsByAsset);

		$splitProvider = self::createStub(SplitProvider::class);
		$splitProvider->method('getSplits')
			->willReturn($splits);

		$calculator = new TaxReportRealizedGainsCalculator($currentTransactionProvider, $splitProvider);

		return $calculator->calculate($this->user, $this->portfolio, $this->yearStart, $this->yearEnd);
	}
}
