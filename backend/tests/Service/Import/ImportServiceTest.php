<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\ImportFile;
use FinGather\Model\Entity\ImportMapping;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Factory\ImportMapperFactoryInterface;
use FinGather\Service\Import\Factory\TransactionRecordFactoryInterface;
use FinGather\Service\Import\ImportService;
use FinGather\Service\Import\Mapper\MapperInterface;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\ImportFileProvider;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Provider\SplitProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\BrokerFixture;
use FinGather\Tests\Fixtures\Model\Entity\CurrencyFixture;
use FinGather\Tests\Fixtures\Model\Entity\GroupFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\SplitDtoFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

#[CoversClass(ImportService::class)]
#[UsesClass(Import::class)]
#[UsesClass(ImportFile::class)]
#[UsesClass(ImportMapping::class)]
#[UsesClass(Broker::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(Transaction::class)]
#[UsesClass(TransactionRecord::class)]
#[UsesClass(TransactionActionTypeEnum::class)]
#[UsesClass(TransactionCreateTypeEnum::class)]
final class ImportServiceTest extends TestCase
{
	private User $user;

	private Portfolio $portfolio;

	private Broker $broker;

	private Asset $asset;

	private Ticker $ticker;

	private Currency $defaultCurrency;

	private Import $import;

	protected function setUp(): void
	{
		$this->user = UserFixture::getUser();
		$this->defaultCurrency = CurrencyFixture::getCurrency(id: 1, code: 'USD');
		$this->portfolio = PortfolioFixture::getPortfolio(currency: $this->defaultCurrency);
		$this->broker = BrokerFixture::getBroker(id: 1);
		$this->ticker = TickerFixture::getTicker();
		$this->asset = AssetFixture::getAsset(ticker: $this->ticker);
		$this->import = new Import(
			user: $this->user,
			portfolio: $this->portfolio,
			created: new DateTimeImmutable(),
			uuid: Uuid::uuid4(),
		);
	}

	// -------------------------------------------------------------------------
	// Happy Path
	// -------------------------------------------------------------------------

	public function testSingleBuyRecordCreatesTransaction(): void
	{
		$record = $this->makeTransactionRecord();
		$transaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable('2024-01-01'),
		);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->willReturn($transaction);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testSellRecordNegatesUnits(): void
	{
		$record = $this->makeTransactionRecord(actionType: 'sell', units: new Decimal('10'));

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::equalTo(TransactionActionTypeEnum::Sell),
				self::anything(),
				self::anything(),
				self::callback(static fn (Decimal $u): bool => $u->equals(new Decimal('-10'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testMultipleFilesUsesEarliestFirstDate(): void
	{
		$earlierDate = new DateTimeImmutable('2024-01-15');
		$laterDate = new DateTimeImmutable('2024-03-01');

		$transactionEarlier = TransactionFixture::getTransaction(actionCreated: $earlierDate);
		$transactionLater = TransactionFixture::getTransaction(actionCreated: $laterDate);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->exactly(2))
			->method('createTransaction')
			->willReturnOnConsecutiveCalls($transactionLater, $transactionEarlier);

		$dataProvider = $this->createMock(DataProvider::class);
		$dataProvider->expects($this->once())
			->method('deleteUserData')
			->with(
				self::anything(),
				self::anything(),
				self::callback(static fn (DateTimeImmutable $d): bool => $d->getTimestamp() === $earlierDate->getTimestamp()),
			);

		$record = $this->makeTransactionRecord();
		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			dataProvider: $dataProvider,
			importFiles: [$this->makeImportFile('file1.csv'), $this->makeImportFile('file2.csv')],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testEmptyImportFilesDoesNotCreateTransactionsOrDeleteData(): void
	{
		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->expects($this->never())->method('createTransaction');

		$dataProvider = $this->createMock(DataProvider::class);
		$dataProvider->expects($this->never())->method('deleteUserData');

		$importProvider = $this->createMock(ImportProvider::class);
		$importProvider->expects($this->once())->method('deleteImport');

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			dataProvider: $dataProvider,
			importProvider: $importProvider,
			importFiles: [],
		);

		$importService->importDataFiles($this->import);
	}

	// -------------------------------------------------------------------------
	// Mapper Resolution
	// -------------------------------------------------------------------------

	public function testMapperNotFoundSkipsFileButProcessesOthers(): void
	{
		$record = $this->makeTransactionRecord();

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())->method('createTransaction')
			->willReturn(TransactionFixture::getTransaction());

		$successMapper = $this->makeMapperStub($record);
		$callCount = 0;
		$failingMapper = $this->createMock(ImportMapperFactoryInterface::class);
		$failingMapper->expects($this->exactly(2))->method('createImportMapper')
			->willReturnCallback(static function () use (&$callCount, $successMapper): MapperInterface {
				$callCount++;
				if ($callCount === 1) {
					throw new \RuntimeException('Import mapper not found');
				}
				return $successMapper;
			});

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importMapperFactory: $failingMapper,
			importFiles: [$this->makeImportFile('fail.csv'), $this->makeImportFile('ok.csv')],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testAllFilesFailMapperDetectionNoTransactionsOrDeleteData(): void
	{
		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->expects($this->never())->method('createTransaction');

		$dataProvider = $this->createMock(DataProvider::class);
		$dataProvider->expects($this->never())->method('deleteUserData');

		$failingFactory = self::createStub(ImportMapperFactoryInterface::class);
		$failingFactory->method('createImportMapper')
			->willThrowException(new \RuntimeException('Import mapper not found'));

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			dataProvider: $dataProvider,
			importMapperFactory: $failingFactory,
			importFiles: [$this->makeImportFile()],
		);

		$importService->importDataFiles($this->import);
	}

	// -------------------------------------------------------------------------
	// Currency Edge Cases
	// -------------------------------------------------------------------------

	public function testValidCurrencyCodeIsResolved(): void
	{
		$eur = CurrencyFixture::getCurrency(id: 2, code: 'EUR');
		$record = $this->makeTransactionRecord(currency: 'EUR', taxCurrency: null, feeCurrency: null);

		$currencyProvider = self::createStub(CurrencyProvider::class);
		$currencyProvider->method('getCurrencyByCode')
			->willReturnMap([['EUR', $eur]]);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::identicalTo($eur),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			currencyProvider: $currencyProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testNullCurrencyFallsBackToPortfolioDefault(): void
	{
		// taxCurrency/feeCurrency also null so getCurrencyByCode is never called at all
		$record = $this->makeTransactionRecord(currency: null, taxCurrency: null, feeCurrency: null);

		$currencyProvider = $this->createMock(CurrencyProvider::class);
		$currencyProvider->expects($this->never())->method('getCurrencyByCode');

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::identicalTo($this->defaultCurrency),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			currencyProvider: $currencyProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testUnknownCurrencySkipsRecord(): void
	{
		$record = $this->makeTransactionRecord(currency: 'XYZ');

		$currencyProvider = self::createStub(CurrencyProvider::class);
		$currencyProvider->method('getCurrencyByCode')->willReturn(null);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->expects($this->never())->method('createTransaction');

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			currencyProvider: $currencyProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testFeeCurrencyUsesDifferentCodeThanTaxCurrency(): void
	{
		$gbp = CurrencyFixture::getCurrency(id: 3, code: 'GBP');
		$eur = CurrencyFixture::getCurrency(id: 4, code: 'EUR');

		// currency: null so it falls back to default without calling getCurrencyByCode for it
		$record = $this->makeTransactionRecord(currency: null, taxCurrency: 'GBP', feeCurrency: 'EUR');

		$currencyProvider = self::createStub(CurrencyProvider::class);
		$currencyProvider->method('getCurrencyByCode')
			->willReturnMap([
				['GBP', $gbp],
				['EUR', $eur],
			]);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				// taxCurrency
				self::identicalTo($gbp),
				self::anything(),
				// feeCurrency — must NOT be $gbp
				self::identicalTo($eur),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			currencyProvider: $currencyProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testNullTaxAndFeeCurrencyFallBackToDefault(): void
	{
		$record = $this->makeTransactionRecord(taxCurrency: null, feeCurrency: null);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				// taxCurrency
				self::identicalTo($this->defaultCurrency),
				self::anything(),
				// feeCurrency
				self::identicalTo($this->defaultCurrency),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	// -------------------------------------------------------------------------
	// Ticker Resolution
	// -------------------------------------------------------------------------

	public function testImportMappingTakesPriorityOverTickerLookup(): void
	{
		$record = $this->makeTransactionRecord(ticker: 'AAPL');
		$mappedTicker = TickerFixture::getTicker(id: 99, ticker: 'AAPL_MAPPED');
		$mapping = new ImportMapping(
			user: $this->user,
			portfolio: $this->portfolio,
			broker: $this->broker,
			importTicker: 'AAPL',
			ticker: $mappedTicker,
		);
		$mappingKey = $this->broker->id . '-AAPL';

		$tickerProvider = $this->createMock(TickerProvider::class);
		$tickerProvider->expects($this->never())->method('getTickerByTicker');
		$tickerProvider->expects($this->never())->method('getTickerByIsin');

		$assetProvider = self::createStub(AssetProvider::class);
		$assetProvider->method('getOrCreateAsset')->willReturn($this->asset);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			tickerProvider: $tickerProvider,
			assetProvider: $assetProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
			importMappings: [$mappingKey => $mapping],
		);

		$importService->importDataFiles($this->import);
	}

	public function testIsinOnlyLookupSucceeds(): void
	{
		$record = $this->makeTransactionRecord(ticker: null, isin: 'US0378331005');

		$tickerProvider = $this->createMock(TickerProvider::class);
		$tickerProvider->expects($this->never())->method('getTickerByTicker');
		$tickerProvider->expects($this->once())
			->method('getTickerByIsin')
			->with('US0378331005', null)
			->willReturn($this->ticker);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			tickerProvider: $tickerProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testIsinOnlyLookupFailsSkipsRecord(): void
	{
		$record = $this->makeTransactionRecord(ticker: null, isin: 'XXINVALID');

		$tickerProvider = self::createStub(TickerProvider::class);
		$tickerProvider->method('getTickerByIsin')->willReturn(null);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->expects($this->never())->method('createTransaction');

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			tickerProvider: $tickerProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testTickerAndIsinFirstLookupSucceeds(): void
	{
		$record = $this->makeTransactionRecord(ticker: 'AAPL', isin: 'US0378331005');

		$tickerProvider = $this->createMock(TickerProvider::class);
		$tickerProvider->expects($this->once())
			->method('getTickerByTicker')
			->with('AAPL', null, 'US0378331005')
			->willReturn($this->ticker);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			tickerProvider: $tickerProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testTickerAndIsinFirstLookupFailsFallsBackToSecond(): void
	{
		$record = $this->makeTransactionRecord(ticker: 'AAPL', isin: 'US0378331005');

		$tickerProvider = $this->createMock(TickerProvider::class);
		$tickerProvider->expects($this->exactly(2))
			->method('getTickerByTicker')
			->willReturnOnConsecutiveCalls(null, $this->ticker);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			tickerProvider: $tickerProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testBothTickerLookupsFailSkipsRecord(): void
	{
		$record = $this->makeTransactionRecord(ticker: 'UNKNOWN');

		$tickerProvider = self::createStub(TickerProvider::class);
		$tickerProvider->method('getTickerByTicker')->willReturn(null);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->expects($this->never())->method('createTransaction');

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			tickerProvider: $tickerProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testNoTickerAndNoIsinSkipsRecord(): void
	{
		$record = $this->makeTransactionRecord(ticker: null, isin: null);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->expects($this->never())->method('createTransaction');

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	// -------------------------------------------------------------------------
	// Price Calculation
	// -------------------------------------------------------------------------

	public function testPriceProvidedDirectlyPassedThrough(): void
	{
		$record = $this->makeTransactionRecord(price: new Decimal('150.00'), total: null);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::callback(static fn (?Decimal $p): bool => $p !== null && $p->equals(new Decimal('150.00'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testPriceNullTotalAndUnitsProvidedCalculatesPrice(): void
	{
		$record = $this->makeTransactionRecord(price: null, total: new Decimal('300'), units: new Decimal('2'));

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::callback(static fn (?Decimal $p): bool => $p !== null && $p->equals(new Decimal('150'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testPriceNullUnitsZeroDividendUsesTotalAsPrice(): void
	{
		$record = $this->makeTransactionRecord(
			actionType: 'dividend',
			price: null,
			total: new Decimal('50'),
			units: null,
		);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::callback(static fn (?Decimal $p): bool => $p !== null && $p->equals(new Decimal('50'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testBothPriceAndTotalNullPassesNullPrice(): void
	{
		$record = $this->makeTransactionRecord(price: null, total: null);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::isNull(),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	// -------------------------------------------------------------------------
	// Split Adjustment
	// -------------------------------------------------------------------------

	public function testIsAdjustedFalseSplitProviderNotCalled(): void
	{
		$record = $this->makeTransactionRecord(isAdjusted: false);

		$splitProvider = $this->createMock(SplitProvider::class);
		$splitProvider->expects($this->never())->method('getSplits');

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			splitProvider: $splitProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testIsAdjustedTrueNoSplitsUnitsAndPriceUnchanged(): void
	{
		$record = $this->makeTransactionRecord(
			isAdjusted: true,
			units: new Decimal('5'),
			price: new Decimal('100'),
		);

		$splitProvider = self::createStub(SplitProvider::class);
		$splitProvider->method('getSplits')->willReturn([]);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::callback(static fn (Decimal $u): bool => $u->equals(new Decimal('5'))),
				self::callback(static fn (?Decimal $p): bool => $p !== null && $p->equals(new Decimal('100'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			splitProvider: $splitProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testOneFutureSplitDividesUnitsAndMultipliesPrice(): void
	{
		$transactionDate = new DateTimeImmutable('2024-01-01');
		$record = $this->makeTransactionRecord(
			isAdjusted: true,
			created: $transactionDate,
			units: new Decimal('4'),
			price: new Decimal('100'),
		);

		$split = SplitDtoFixture::getSplitDto(
			date: new DateTimeImmutable('2024-06-01'),
			factor: new Decimal(4),
		);

		$splitProvider = self::createStub(SplitProvider::class);
		$splitProvider->method('getSplits')->willReturn([$split]);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::callback(static fn (Decimal $u): bool => $u->equals(new Decimal('1'))),
				self::callback(static fn (?Decimal $p): bool => $p !== null && $p->equals(new Decimal('400'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			splitProvider: $splitProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testSplitBeforeTransactionNotApplied(): void
	{
		$transactionDate = new DateTimeImmutable('2024-06-01');
		$record = $this->makeTransactionRecord(
			isAdjusted: true,
			created: $transactionDate,
			units: new Decimal('4'),
			price: new Decimal('100'),
		);

		$split = SplitDtoFixture::getSplitDto(
			date: new DateTimeImmutable('2024-01-01'),
			factor: new Decimal(4),
		);

		$splitProvider = self::createStub(SplitProvider::class);
		$splitProvider->method('getSplits')->willReturn([$split]);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::callback(static fn (Decimal $u): bool => $u->equals(new Decimal('4'))),
				self::callback(static fn (?Decimal $p): bool => $p !== null && $p->equals(new Decimal('100'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			splitProvider: $splitProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testMultipleFutureSplitsAndOnePastAllApplied(): void
	{
		$transactionDate = new DateTimeImmutable('2024-01-01');
		$record = $this->makeTransactionRecord(
			isAdjusted: true,
			created: $transactionDate,
			units: new Decimal('6'),
			price: new Decimal('30'),
		);

		$splits = [
			// past — skipped
			SplitDtoFixture::getSplitDto(date: new DateTimeImmutable('2023-01-01'), factor: new Decimal(5)),
			// future
			SplitDtoFixture::getSplitDto(date: new DateTimeImmutable('2024-06-01'), factor: new Decimal(2)),
			// future
			SplitDtoFixture::getSplitDto(date: new DateTimeImmutable('2024-09-01'), factor: new Decimal(3)),
		];

		$splitProvider = self::createStub(SplitProvider::class);
		$splitProvider->method('getSplits')->willReturn($splits);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				// 6/2/3
				self::callback(static fn (Decimal $u): bool => $u->equals(new Decimal('1'))),
				// 30*2*3
				self::callback(static fn (?Decimal $p): bool => $p !== null && $p->equals(new Decimal('180'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			splitProvider: $splitProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testIsAdjustedTrueWithNullPricePriceComputedFromAdjustedUnits(): void
	{
		$transactionDate = new DateTimeImmutable('2024-01-01');
		$record = $this->makeTransactionRecord(
			isAdjusted: true,
			created: $transactionDate,
			price: null,
			total: new Decimal('100'),
			units: new Decimal('2'),
		);

		$split = SplitDtoFixture::getSplitDto(
			date: new DateTimeImmutable('2024-06-01'),
			factor: new Decimal(2),
		);

		$splitProvider = self::createStub(SplitProvider::class);
		$splitProvider->method('getSplits')->willReturn([$split]);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				// 2/2
				self::callback(static fn (Decimal $u): bool => $u->equals(new Decimal('1'))),
				// 100/1
				self::callback(static fn (?Decimal $p): bool => $p !== null && $p->equals(new Decimal('100'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			splitProvider: $splitProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	// -------------------------------------------------------------------------
	// Deduplication
	// -------------------------------------------------------------------------

	public function testImportIdentifierFoundInDbSkipsRecord(): void
	{
		$record = $this->makeTransactionRecord(importIdentifier: 'TXN-001');

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->expects($this->once())->method('getTransactionByIdentifier')
			->with($this->broker->id, 'TXN-001')
			->willReturn(TransactionFixture::getTransaction());
		$transactionProvider->expects($this->never())->method('createTransaction');

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testNullImportIdentifierSkipsDeduplicationCheck(): void
	{
		$record = $this->makeTransactionRecord(importIdentifier: null);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->expects($this->never())->method('getTransactionByIdentifier');
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	// -------------------------------------------------------------------------
	// Action Type
	// -------------------------------------------------------------------------

	public function testEmptyActionTypeBecomesUndefinedAndUnitsNotNegated(): void
	{
		$record = $this->makeTransactionRecord(actionType: '', units: new Decimal('5'));

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::equalTo(TransactionActionTypeEnum::Undefined),
				self::anything(),
				self::anything(),
				self::callback(static fn (Decimal $u): bool => $u->equals(new Decimal('5'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testDividendActionTypeUnitsNotNegated(): void
	{
		$record = $this->makeTransactionRecord(actionType: 'dividend', units: new Decimal('5'));

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::equalTo(TransactionActionTypeEnum::Dividend),
				self::anything(),
				self::anything(),
				self::callback(static fn (Decimal $u): bool => $u->equals(new Decimal('5'))),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	public function testDividendTaxActionTypeParsed(): void
	{
		$record = $this->makeTransactionRecord(actionType: 'dividend tax');

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->with(
				self::anything(),
				self::anything(),
				self::anything(),
				self::anything(),
				self::equalTo(TransactionActionTypeEnum::DividendTax),
			)
			->willReturn(TransactionFixture::getTransaction());

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			importFiles: [$this->makeImportFile()],
			transactionRecord: $record,
		);

		$importService->importDataFiles($this->import);
	}

	// -------------------------------------------------------------------------
	// Batch Behaviour
	// -------------------------------------------------------------------------

	public function testMixedRecordsOnlyValidOneCreatesTransaction(): void
	{
		$validRecord = $this->makeTransactionRecord(importIdentifier: null, currency: 'USD');
		$unknownCurrencyRecord = $this->makeTransactionRecord(importIdentifier: null, currency: 'XYZ');
		$duplicateRecord = $this->makeTransactionRecord(importIdentifier: 'DUP-001');

		$currencyProvider = self::createStub(CurrencyProvider::class);
		$currencyProvider->method('getCurrencyByCode')
			->willReturnMap([
				['USD', $this->defaultCurrency],
				['XYZ', null],
			]);

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')
			->willReturnCallback(
				static fn (?int $brokerId, string $id): ?Transaction => $id === 'DUP-001' ? TransactionFixture::getTransaction() : null,
			);
		$transactionProvider->expects($this->once())
			->method('createTransaction')
			->willReturn(TransactionFixture::getTransaction());

		$records = [$unknownCurrencyRecord, $duplicateRecord, $validRecord];
		$mapperFactory = self::createStub(ImportMapperFactoryInterface::class);
		$mapperStub = self::createStub(MapperInterface::class);
		$mapperStub->method('getImportType')->willReturn(BrokerImportTypeEnum::Trading212);
		$mapperStub->method('getAllowedMarketIds')->willReturn(null);
		$mapperStub->method('getRecords')->willReturn([['row' => '1'], ['row' => '2'], ['row' => '3']]);
		$mapperFactory->method('createImportMapper')->willReturn($mapperStub);

		$transactionRecordFactory = self::createStub(TransactionRecordFactoryInterface::class);
		$transactionRecordFactory->method('createFromCsvRecord')
			->willReturnOnConsecutiveCalls(...$records);

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			currencyProvider: $currencyProvider,
			importMapperFactory: $mapperFactory,
			transactionRecordFactory: $transactionRecordFactory,
			importFiles: [$this->makeImportFile()],
		);

		$importService->importDataFiles($this->import);
	}

	public function testThreeValidRecordsDeleteUserDataUsesMinimumDate(): void
	{
		$date1 = new DateTimeImmutable('2024-03-10');
		$date2 = new DateTimeImmutable('2024-01-05');
		$date3 = new DateTimeImmutable('2024-02-20');

		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$transactionProvider->expects($this->exactly(3))
			->method('createTransaction')
			->willReturnOnConsecutiveCalls(
				TransactionFixture::getTransaction(actionCreated: $date1),
				TransactionFixture::getTransaction(actionCreated: $date2),
				TransactionFixture::getTransaction(actionCreated: $date3),
			);

		$dataProvider = $this->createMock(DataProvider::class);
		$dataProvider->expects($this->once())
			->method('deleteUserData')
			->with(
				self::anything(),
				self::anything(),
				self::callback(static fn (DateTimeImmutable $d): bool => $d->getTimestamp() === $date2->getTimestamp()),
			);

		$records = [
			$this->makeTransactionRecord(),
			$this->makeTransactionRecord(),
			$this->makeTransactionRecord(),
		];

		$mapperFactory = self::createStub(ImportMapperFactoryInterface::class);
		$mapperStub = self::createStub(MapperInterface::class);
		$mapperStub->method('getImportType')->willReturn(BrokerImportTypeEnum::Trading212);
		$mapperStub->method('getAllowedMarketIds')->willReturn(null);
		$mapperStub->method('getRecords')->willReturn([['row' => '1'], ['row' => '2'], ['row' => '3']]);
		$mapperFactory->method('createImportMapper')->willReturn($mapperStub);

		$transactionRecordFactory = self::createStub(TransactionRecordFactoryInterface::class);
		$transactionRecordFactory->method('createFromCsvRecord')
			->willReturnOnConsecutiveCalls(...$records);

		$importService = $this->createImportService(
			transactionProvider: $transactionProvider,
			dataProvider: $dataProvider,
			importMapperFactory: $mapperFactory,
			transactionRecordFactory: $transactionRecordFactory,
			importFiles: [$this->makeImportFile()],
		);

		$importService->importDataFiles($this->import);
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * @param ImportFile[] $importFiles
	 * @param array<string, ImportMapping> $importMappings
	 */
	private function createImportService(
		?TransactionProvider $transactionProvider = null,
		?TickerProvider $tickerProvider = null,
		?AssetProvider $assetProvider = null,
		?CurrencyProvider $currencyProvider = null,
		?DataProvider $dataProvider = null,
		?ImportProvider $importProvider = null,
		?ImportMapperFactoryInterface $importMapperFactory = null,
		?TransactionRecordFactoryInterface $transactionRecordFactory = null,
		?SplitProvider $splitProvider = null,
		array $importFiles = [],
		?TransactionRecord $transactionRecord = null,
		array $importMappings = [],
	): ImportService {
		$defaultRecord = $transactionRecord ?? $this->makeTransactionRecord();

		$defaultMapperStub = self::createStub(MapperInterface::class);
		$defaultMapperStub->method('getImportType')->willReturn(BrokerImportTypeEnum::Trading212);
		$defaultMapperStub->method('getAllowedMarketIds')->willReturn(null);
		$defaultMapperStub->method('getRecords')->willReturn([['row' => '1']]);

		$defaultMapperFactory = self::createStub(ImportMapperFactoryInterface::class);
		$defaultMapperFactory->method('createImportMapper')->willReturn($defaultMapperStub);

		$defaultTransactionRecordFactory = self::createStub(TransactionRecordFactoryInterface::class);
		$defaultTransactionRecordFactory->method('createFromCsvRecord')->willReturn($defaultRecord);

		$defaultTickerProvider = self::createStub(TickerProvider::class);
		$defaultTickerProvider->method('getTickerByTicker')->willReturn($this->ticker);
		$defaultTickerProvider->method('getTickerByIsin')->willReturn($this->ticker);

		$defaultAssetProvider = self::createStub(AssetProvider::class);
		$defaultAssetProvider->method('getOrCreateAsset')->willReturn($this->asset);

		$defaultCurrencyProvider = self::createStub(CurrencyProvider::class);
		$defaultCurrencyProvider->method('getCurrencyByCode')->willReturn($this->defaultCurrency);

		$defaultGroupProvider = self::createStub(GroupProvider::class);
		$defaultGroupProvider->method('getOthersGroup')->willReturn(GroupFixture::getGroup());

		$defaultBrokerProvider = self::createStub(BrokerProvider::class);
		$defaultBrokerProvider->method('getBrokerByImportType')->willReturn($this->broker);

		$defaultImportMappingProvider = self::createStub(ImportMappingProvider::class);
		$defaultImportMappingProvider->method('getImportMappings')->willReturn($importMappings);

		$defaultImportFileProvider = self::createStub(ImportFileProvider::class);
		$defaultImportFileProvider->method('getImportFiles')
			->willReturn(new ArrayIterator($importFiles));

		$defaultImportProvider = self::createStub(ImportProvider::class);
		$defaultDataProvider = self::createStub(DataProvider::class);
		$defaultSplitProvider = self::createStub(SplitProvider::class);
		$defaultSplitProvider->method('getSplits')->willReturn([]);

		$defaultTransactionProvider = self::createStub(TransactionProvider::class);
		$defaultTransactionProvider->method('getTransactionByIdentifier')->willReturn(null);
		$defaultTransactionProvider->method('createTransaction')->willReturn(TransactionFixture::getTransaction());

		return new ImportService(
			transactionProvider: $transactionProvider ?? $defaultTransactionProvider,
			tickerProvider: $tickerProvider ?? $defaultTickerProvider,
			assetProvider: $assetProvider ?? $defaultAssetProvider,
			currencyProvider: $currencyProvider ?? $defaultCurrencyProvider,
			groupProvider: $defaultGroupProvider,
			dataProvider: $dataProvider ?? $defaultDataProvider,
			importProvider: $importProvider ?? $defaultImportProvider,
			importFileProvider: $defaultImportFileProvider,
			importMappingProvider: $defaultImportMappingProvider,
			brokerProvider: $defaultBrokerProvider,
			importMapperFactory: $importMapperFactory ?? $defaultMapperFactory,
			transactionRecordFactory: $transactionRecordFactory ?? $defaultTransactionRecordFactory,
			splitProvider: $splitProvider ?? $defaultSplitProvider,
			logger: self::createStub(LoggerInterface::class),
		);
	}

	private function makeImportFile(string $fileName = 'test.csv'): ImportFile
	{
		return new ImportFile(
			import: $this->import,
			created: new DateTimeImmutable(),
			fileName: $fileName,
			contents: 'dummy',
		);
	}

	private function makeMapperStub(TransactionRecord $record): MapperInterface
	{
		$mapper = self::createStub(MapperInterface::class);
		$mapper->method('getImportType')->willReturn(BrokerImportTypeEnum::Trading212);
		$mapper->method('getAllowedMarketIds')->willReturn(null);
		$mapper->method('getRecords')->willReturn([['row' => '1']]);

		$factory = self::createStub(TransactionRecordFactoryInterface::class);
		$factory->method('createFromCsvRecord')->willReturn($record);

		return $mapper;
	}

	private function makeTransactionRecord(
		?string $ticker = 'AAPL',
		?string $isin = null,
		?string $actionType = 'buy',
		?DateTimeImmutable $created = null,
		Decimal|null|false $units = false,
		Decimal|null|false $price = false,
		?Decimal $total = null,
		?string $currency = 'USD',
		?Decimal $tax = null,
		?string $taxCurrency = 'USD',
		?Decimal $fee = null,
		?string $feeCurrency = 'USD',
		?string $notes = null,
		?bool $isAdjusted = null,
		?string $importIdentifier = 'TXN-TEST',
	): TransactionRecord {
		return new TransactionRecord(
			ticker: $ticker,
			isin: $isin,
			actionType: $actionType,
			created: $created ?? new DateTimeImmutable('2024-01-01'),
			units: $units === false ? new Decimal('5') : $units,
			price: $price === false ? new Decimal('100') : $price,
			total: $total,
			currency: $currency,
			tax: $tax,
			taxCurrency: $taxCurrency,
			fee: $fee,
			feeCurrency: $feeCurrency,
			notes: $notes,
			isAdjusted: $isAdjusted,
			importIdentifier: $importIdentifier,
		);
	}
}
