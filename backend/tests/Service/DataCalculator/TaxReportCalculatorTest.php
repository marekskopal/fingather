<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\TaxReportDividendsByCountryDto;
use FinGather\Service\DataCalculator\Dto\TaxReportDividendsDto;
use FinGather\Service\DataCalculator\Dto\TaxReportDividendTransactionDto;
use FinGather\Service\DataCalculator\Dto\TaxReportDto;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainsDto;
use FinGather\Service\DataCalculator\Dto\TaxReportUnrealizedDto;
use FinGather\Service\DataCalculator\Dto\TaxReportUnrealizedPositionDto;
use FinGather\Service\DataCalculator\TaxReportCalculator;
use FinGather\Service\DataCalculator\TaxReportRealizedGainsCalculatorInterface;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\CurrentTransactionProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\CountryFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxReportCalculator::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(Transaction::class)]
#[UsesClass(User::class)]
#[UsesClass(TaxReportDto::class)]
#[UsesClass(TaxReportRealizedGainsDto::class)]
#[UsesClass(TaxReportUnrealizedDto::class)]
#[UsesClass(TaxReportUnrealizedPositionDto::class)]
#[UsesClass(TaxReportDividendsDto::class)]
#[UsesClass(TaxReportDividendTransactionDto::class)]
#[UsesClass(TaxReportDividendsByCountryDto::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Sector::class)]
#[UsesClass(AssetDataDto::class)]
#[UsesClass(Country::class)]
final class TaxReportCalculatorTest extends TestCase
{
	private readonly User $user;

	private readonly Portfolio $portfolio;

	private readonly TaxReportRealizedGainsDto $emptyRealizedGains;

	protected function setUp(): void
	{
		$this->user = UserFixture::getUser();
		$this->portfolio = PortfolioFixture::getPortfolio();
		$this->emptyRealizedGains = new TaxReportRealizedGainsDto(
			method: CostBasisMethodEnum::Fifo,
			totalSalesProceeds: new Decimal(0),
			totalCostBasis: new Decimal(0),
			totalGains: new Decimal(0),
			totalLosses: new Decimal(0),
			totalFees: new Decimal(0),
			netRealizedGainLoss: new Decimal(0),
			transactions: [],
		);
	}

	public function testCalculateReturnsCorrectYear(): void
	{
		$result = $this->calculate(year: 2024);

		self::assertSame(2024, $result->year);
	}

	public function testCalculateForCurrentYearExcludesFutureDatedTransactions(): void
	{
		$currentYear = (int) (new DateTimeImmutable())->format('Y');
		$farFuture = new DateTimeImmutable($currentYear . '-12-31 12:00:00');

		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable($currentYear . '-01-02'),
					feeDefaultCurrency: new Decimal(7),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: $farFuture,
					feeDefaultCurrency: new Decimal(99),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		// Skip if today is Dec 31 itself — that would invalidate the assumption.
		if ($farFuture <= new DateTimeImmutable()) {
			self::markTestSkipped('Test only meaningful before Dec 31 of the current year.');
		}

		$result = $this->calculate(year: $currentYear, transactionsByAsset: $transactionsByAsset);

		self::assertSame($currentYear, $result->year);
		// Future-dated fee must be excluded (yearEnd = now), only the early-year one counts.
		self::assertSame(7.0, $result->totalFees->toFloat());
	}

	public function testCalculateForPastYearIncludesDecemberTransactions(): void
	{
		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2024-12-31 23:00:00'),
					feeDefaultCurrency: new Decimal(11),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		// Past-year report keeps yearEnd = Dec 31 23:59:59, so Dec 31 transactions count.
		self::assertSame(11.0, $result->totalFees->toFloat());
	}

	public function testCalculateNoTransactionsReturnsZeros(): void
	{
		$result = $this->calculate();

		self::assertSame(0.0, $result->totalFees->toFloat());
		self::assertSame(0.0, $result->totalTaxes->toFloat());
		self::assertSame(0.0, $result->dividends->totalGross->toFloat());
		self::assertSame(0.0, $result->dividends->totalTax->toFloat());
		self::assertSame(0.0, $result->dividends->totalNet->toFloat());
		self::assertCount(0, $result->dividends->transactions);
		self::assertCount(0, $result->dividends->dividendsByCountry);
		self::assertSame(0.0, $result->unrealizedPositions->totalMarketValue->toFloat());
		self::assertSame(0.0, $result->unrealizedPositions->totalCostBasis->toFloat());
		self::assertSame(0.0, $result->unrealizedPositions->totalGainLoss->toFloat());
		self::assertCount(0, $result->unrealizedPositions->positions);
	}

	public function testCalculateTotalFeesAggregatesAllTransactionsInYear(): void
	{
		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2024-03-01'),
					feeDefaultCurrency: new Decimal(5),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2024-07-15'),
					feeDefaultCurrency: new Decimal(3),
					taxDefaultCurrency: new Decimal(0),
				),
			],
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Sell,
					actionCreated: new DateTimeImmutable('2024-11-01'),
					feeDefaultCurrency: new Decimal(7),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertSame(15.0, $result->totalFees->toFloat());
	}

	public function testCalculateTotalFeesExcludesTransactionsOutsideYear(): void
	{
		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2023-12-31'),
					feeDefaultCurrency: new Decimal(10),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2024-01-01'),
					feeDefaultCurrency: new Decimal(4),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2025-01-01'),
					feeDefaultCurrency: new Decimal(8),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertSame(4.0, $result->totalFees->toFloat());
	}

	public function testCalculateTotalTaxesAggregatesAllTransactionsInYear(): void
	{
		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2024-03-01'),
					taxDefaultCurrency: new Decimal(2),
					feeDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2024-09-01'),
					taxDefaultCurrency: new Decimal(3),
					feeDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertSame(5.0, $result->totalTaxes->toFloat());
	}

	public function testCalculateTotalTaxesExcludesTransactionsOutsideYear(): void
	{
		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2023-06-01'),
					taxDefaultCurrency: new Decimal(100),
					feeDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Buy,
					actionCreated: new DateTimeImmutable('2024-06-01'),
					taxDefaultCurrency: new Decimal(6),
					feeDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertSame(6.0, $result->totalTaxes->toFloat());
	}

	public function testCalculateDividendsWithTaxCalculatesNetCorrectly(): void
	{
		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2024-05-01'),
					priceDefaultCurrency: new Decimal(100),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::DividendTax,
					actionCreated: new DateTimeImmutable('2024-05-01'),
					priceDefaultCurrency: new Decimal(-15),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertSame(100.0, $result->dividends->totalGross->toFloat());
		self::assertSame(15.0, $result->dividends->totalTax->toFloat());
		self::assertSame(85.0, $result->dividends->totalNet->toFloat());
		self::assertCount(1, $result->dividends->transactions);
	}

	public function testCalculateDividendsWithoutTaxHasZeroTax(): void
	{
		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2024-05-01'),
					priceDefaultCurrency: new Decimal(50),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertSame(50.0, $result->dividends->totalGross->toFloat());
		self::assertSame(0.0, $result->dividends->totalTax->toFloat());
		self::assertSame(50.0, $result->dividends->totalNet->toFloat());
	}

	public function testCalculateDividendsExcludesTransactionsOutsideYear(): void
	{
		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2023-12-31'),
					priceDefaultCurrency: new Decimal(200),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2024-06-01'),
					priceDefaultCurrency: new Decimal(75),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2025-01-01'),
					priceDefaultCurrency: new Decimal(300),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertSame(75.0, $result->dividends->totalGross->toFloat());
		self::assertCount(1, $result->dividends->transactions);
	}

	public function testCalculateDividendsByCountryAggregatesCorrectly(): void
	{
		$usTicker = TickerFixture::getTicker(
			id: 1,
			ticker: 'AAPL',
			name: 'Apple Inc.',
			country: CountryFixture::getCountry(isoCode: 'US', name: 'United States'),
		);
		$gbTicker = TickerFixture::getTicker(
			id: 2,
			ticker: 'SHEL',
			name: 'Shell plc',
			country: CountryFixture::getCountry(isoCode: 'GB', name: 'United Kingdom'),
		);

		$usAsset = AssetFixture::getAsset(id: 1, ticker: $usTicker);
		$gbAsset = AssetFixture::getAsset(id: 2, ticker: $gbTicker);

		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					asset: $usAsset,
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2024-03-01'),
					priceDefaultCurrency: new Decimal(60),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					asset: $usAsset,
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2024-06-01'),
					priceDefaultCurrency: new Decimal(40),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
			],
			[
				TransactionFixture::getTransaction(
					asset: $gbAsset,
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2024-09-01'),
					priceDefaultCurrency: new Decimal(80),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertSame(180.0, $result->dividends->totalGross->toFloat());
		self::assertCount(2, $result->dividends->dividendsByCountry);

		$byCountry = [];
		foreach ($result->dividends->dividendsByCountry as $countryDto) {
			$byCountry[$countryDto->countryIsoCode] = $countryDto;
		}

		self::assertArrayHasKey('US', $byCountry);
		self::assertSame(100.0, $byCountry['US']->totalGross->toFloat());

		self::assertArrayHasKey('GB', $byCountry);
		self::assertSame(80.0, $byCountry['GB']->totalGross->toFloat());
	}

	public function testCalculateDividendTransactionDtoHasCorrectFields(): void
	{
		$date = new DateTimeImmutable('2024-05-15');

		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: $date,
					priceDefaultCurrency: new Decimal(120),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::DividendTax,
					actionCreated: $date,
					priceDefaultCurrency: new Decimal(-18),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertCount(1, $result->dividends->transactions);

		$tx = $result->dividends->transactions[0];
		self::assertSame('AAPL', $tx->tickerTicker);
		self::assertSame('Apple Inc.', $tx->tickerName);
		self::assertSame('United States', $tx->countryName);
		self::assertSame('US', $tx->countryIsoCode);
		self::assertSame('2024-05-15', $tx->date);
		self::assertSame(120.0, $tx->grossAmount->toFloat());
		self::assertSame(18.0, $tx->tax->toFloat());
		self::assertSame(102.0, $tx->netAmount->toFloat());
	}

	public function testCalculateMultipleDividendsSameDateDistributesTaxProportionally(): void
	{
		// Two dividends on the same date: 200 and 100, with total tax of 30
		// Tax should be split proportionally: 200/(200+100)*30=20, 100/(200+100)*30=10
		$transactionsByAsset = [
			[
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2024-05-01'),
					priceDefaultCurrency: new Decimal(200),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::Dividend,
					actionCreated: new DateTimeImmutable('2024-05-01'),
					priceDefaultCurrency: new Decimal(100),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
				TransactionFixture::getTransaction(
					actionType: TransactionActionTypeEnum::DividendTax,
					actionCreated: new DateTimeImmutable('2024-05-01'),
					priceDefaultCurrency: new Decimal(-30),
					feeDefaultCurrency: new Decimal(0),
					taxDefaultCurrency: new Decimal(0),
				),
			],
		];

		$result = $this->calculate(year: 2024, transactionsByAsset: $transactionsByAsset);

		self::assertSame(300.0, $result->dividends->totalGross->toFloat());
		self::assertSame(30.0, $result->dividends->totalTax->toFloat());
		self::assertSame(270.0, $result->dividends->totalNet->toFloat());
		self::assertCount(2, $result->dividends->transactions);

		// First dividend: tax = 30 * 200/300 = 20
		self::assertSame(200.0, $result->dividends->transactions[0]->grossAmount->toFloat());
		self::assertSame(20.0, $result->dividends->transactions[0]->tax->toFloat());
		self::assertSame(180.0, $result->dividends->transactions[0]->netAmount->toFloat());

		// Second dividend: tax = 30 * 100/300 = 10
		self::assertSame(100.0, $result->dividends->transactions[1]->grossAmount->toFloat());
		self::assertSame(10.0, $result->dividends->transactions[1]->tax->toFloat());
		self::assertSame(90.0, $result->dividends->transactions[1]->netAmount->toFloat());
	}

	public function testCalculateUnrealizedPositionsIncludesOpenAssets(): void
	{
		$asset = AssetFixture::getAsset();
		$firstBuyDate = new DateTimeImmutable('2024-01-15');

		$assetData = $this->createAssetDataDto(
			units: new Decimal(10),
			value: new Decimal(1500),
			transactionValueDefaultCurrency: new Decimal(1000),
			gainDefaultCurrency: new Decimal(500),
			averagePriceDefaultCurrency: new Decimal(100),
			firstTransactionActionCreated: $firstBuyDate,
		);

		$result = $this->calculate(
			year: 2024,
			assets: [$asset],
			assetData: [$asset->id => $assetData],
		);

		self::assertSame(1500.0, $result->unrealizedPositions->totalMarketValue->toFloat());
		self::assertSame(1000.0, $result->unrealizedPositions->totalCostBasis->toFloat());
		self::assertSame(500.0, $result->unrealizedPositions->totalGainLoss->toFloat());
		self::assertCount(1, $result->unrealizedPositions->positions);

		$pos = $result->unrealizedPositions->positions[0];
		self::assertSame('AAPL', $pos->tickerTicker);
		self::assertSame('Apple Inc.', $pos->tickerName);
		self::assertSame('2024-01-15', $pos->firstBuyDate);
		self::assertSame(10.0, $pos->units->toFloat());
		self::assertSame(100.0, $pos->buyPrice->toFloat());
		self::assertSame(1000.0, $pos->costBasis->toFloat());
		self::assertSame(1500.0, $pos->marketValue->toFloat());
		self::assertSame(500.0, $pos->gainLoss->toFloat());
	}

	public function testCalculateUnrealizedPositionsSkipsClosedAssets(): void
	{
		$asset = AssetFixture::getAsset();

		$assetData = $this->createAssetDataDto(units: new Decimal(0));

		$result = $this->calculate(
			year: 2024,
			assets: [$asset],
			assetData: [$asset->id => $assetData],
		);

		self::assertCount(0, $result->unrealizedPositions->positions);
		self::assertSame(0.0, $result->unrealizedPositions->totalMarketValue->toFloat());
	}

	public function testCalculateUnrealizedPositionsSkipsNullAssetData(): void
	{
		$asset = AssetFixture::getAsset();

		$result = $this->calculate(
			year: 2024,
			assets: [$asset],
			assetData: [],
		);

		self::assertCount(0, $result->unrealizedPositions->positions);
	}

	public function testCalculateUnrealizedPositionsTotalsMultipleAssets(): void
	{
		$asset1 = AssetFixture::getAsset(id: 1);
		$asset2 = AssetFixture::getAsset(id: 2);

		$assetData1 = $this->createAssetDataDto(
			units: new Decimal(10),
			value: new Decimal(1000),
			transactionValueDefaultCurrency: new Decimal(800),
			gainDefaultCurrency: new Decimal(200),
		);
		$assetData2 = $this->createAssetDataDto(
			units: new Decimal(5),
			value: new Decimal(500),
			transactionValueDefaultCurrency: new Decimal(400),
			gainDefaultCurrency: new Decimal(100),
		);

		$result = $this->calculate(
			year: 2024,
			assets: [$asset1, $asset2],
			assetData: [
				$asset1->id => $assetData1,
				$asset2->id => $assetData2,
			],
		);

		self::assertSame(1500.0, $result->unrealizedPositions->totalMarketValue->toFloat());
		self::assertSame(1200.0, $result->unrealizedPositions->totalCostBasis->toFloat());
		self::assertSame(300.0, $result->unrealizedPositions->totalGainLoss->toFloat());
		self::assertCount(2, $result->unrealizedPositions->positions);
	}

	/**
	 * @param list<list<Transaction>> $transactionsByAsset
	 * @param list<Asset> $assets
	 * @param array<int, AssetDataDto> $assetData
	 */
	private function calculate(
		int $year = 2024,
		array $transactionsByAsset = [],
		array $assets = [],
		array $assetData = [],
		?TaxReportRealizedGainsDto $realizedGains = null,
	): TaxReportDto {
		$currentTransactionProvider = self::createStub(CurrentTransactionProviderInterface::class);
		$currentTransactionProvider->method('loadTransactions')
			->willReturn($transactionsByAsset);

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')
			->willReturn(new ArrayIterator($assets));

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('getAssetData')
			->willReturnCallback(static fn (mixed $user, mixed $portfolio, Asset $asset) => $assetData[$asset->id] ?? null);

		$realizedGainsCalculator = self::createStub(TaxReportRealizedGainsCalculatorInterface::class);
		$realizedGainsCalculator->method('calculate')
			->willReturn($realizedGains ?? $this->emptyRealizedGains);

		$calculator = new TaxReportCalculator($currentTransactionProvider, $assetProvider, $assetDataProvider, $realizedGainsCalculator);

		return $calculator->calculate($this->user, $this->portfolio, $year);
	}

	private function createAssetDataDto(
		?Decimal $units = null,
		?Decimal $value = null,
		?Decimal $transactionValueDefaultCurrency = null,
		?Decimal $gainDefaultCurrency = null,
		?Decimal $averagePriceDefaultCurrency = null,
		?DateTimeImmutable $firstTransactionActionCreated = null,
	): AssetDataDto {
		return new AssetDataDto(
			date: new DateTimeImmutable('2024-12-31'),
			price: new Decimal(100),
			units: $units ?? new Decimal(10),
			value: $value ?? new Decimal(1000),
			transactionValue: new Decimal(800),
			transactionValueDefaultCurrency: $transactionValueDefaultCurrency ?? new Decimal(800),
			averagePrice: new Decimal(80),
			averagePriceDefaultCurrency: $averagePriceDefaultCurrency ?? new Decimal(80),
			gain: new Decimal(200),
			gainDefaultCurrency: $gainDefaultCurrency ?? new Decimal(200),
			realizedGain: new Decimal(0),
			realizedGainDefaultCurrency: new Decimal(0),
			gainPercentage: 25.0,
			gainPercentagePerAnnum: 25.0,
			dividendYield: new Decimal(0),
			dividendYieldDefaultCurrency: new Decimal(0),
			dividendYieldPercentage: 0.0,
			dividendYieldPercentagePerAnnum: 0.0,
			fxImpact: new Decimal(0),
			fxImpactPercentage: 0.0,
			fxImpactPercentagePerAnnum: 0.0,
			return: new Decimal(200),
			returnPercentage: 25.0,
			returnPercentagePerAnnum: 25.0,
			tax: new Decimal(0),
			taxDefaultCurrency: new Decimal(0),
			fee: new Decimal(0),
			feeDefaultCurrency: new Decimal(0),
			firstTransactionActionCreated: $firstTransactionActionCreated ?? new DateTimeImmutable('2024-01-01'),
		);
	}
}
