<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use Closure;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\XtbMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(XtbMapper::class)]
#[UsesClass(MappingDto::class)]
final class XtbMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'xtb_export.xlsx';

	public function testGetImportType(): void
	{
		$mapper = new XtbMapper();
		self::assertSame(BrokerImportTypeEnum::Xtb, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new XtbMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->ticker);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->importIdentifier);
		self::assertNotNull($mapping->country);
	}

	/** @return iterable<string, array{string, string|null}> */
	public static function symbolCountryProvider(): iterable
	{
		yield 'german' => ['BRYN.DE', 'DE'];
		yield 'french' => ['MC.FR', 'FR'];
		yield 'uk uses ISO GB' => ['CBU0.UK', 'GB'];
		yield 'us' => ['AAPL.US', 'US'];
		yield 'dutch' => ['ASML.NL', 'NL'];
		yield 'unknown suffix' => ['FOO.XX', null];
		yield 'no suffix' => ['FOO', null];
	}

	#[DataProvider('symbolCountryProvider')]
	public function testCountryClosureMapsSuffixToIsoCountry(string $symbol, ?string $expected): void
	{
		$mapper = new XtbMapper();
		$mapping = $mapper->getMapping();

		self::assertInstanceOf(Closure::class, $mapping->country);
		self::assertSame($expected, ($mapping->country)(['Symbol' => $symbol]));
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new XtbMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetRecordsParsesSimpleAndPartialOperations(): void
	{
		$records = $this->getXtbRecords();

		// Simple format: "OPEN BUY 0.1823 @ 257.80"
		$simpleBuy = $this->findRecordById($records, '1089479372');
		self::assertSame('BUY', $simpleBuy['Type']);
		self::assertSame('0.1823', $simpleBuy['Volume']);
		self::assertSame('AAPL.US', $simpleBuy['Symbol']);
		self::assertSame('982.94', $simpleBuy['Total']);
		self::assertSame('CZK', $simpleBuy['Currency']);

		// Simple format: "CLOSE BUY 0.1823 @ 257.89"
		$simpleSell = $this->findRecordById($records, '1089480050');
		self::assertSame('SELL', $simpleSell['Type']);
		self::assertSame('0.1823', $simpleSell['Volume']);

		// Partial open: "OPEN BUY 2/2.5119 @ 12.85" — only the first number is the trade volume.
		$partialBuy = $this->findRecordById($records, '1100000001');
		self::assertSame('BUY', $partialBuy['Type']);
		self::assertSame('2', $partialBuy['Volume']);
		self::assertSame('ABC.US', $partialBuy['Symbol']);

		// Partial close: "CLOSE BUY 0.4511/0.5846 @ 1094.50".
		// Without partial-close support these rows were dropped, leaving the position
		// open even though it had been (partially) sold.
		$partialSell = $this->findRecordById($records, '1100000003');
		self::assertSame('SELL', $partialSell['Type']);
		self::assertSame('0.4511', $partialSell['Volume']);
		self::assertSame('RHM.DE', $partialSell['Symbol']);
	}

	public function testGetRecordsAddsCloseTradeAmountToSellTotal(): void
	{
		// XTB splits sells across two rows: a "close trade" with the realised P/L
		// and a "Stock sale" whose amount is just the released cost basis. The
		// gross proceeds — what we want to import as Total — is the sum of the two.
		$records = $this->getXtbRecords();

		// Stock sale id=1089480050 amount=982.94, paired with close trade id=1089480049 amount=-10.14.
		// Without the fix Total would be 982.94 (cost basis), giving a sell price equal to the buy price.
		$simpleSell = $this->findRecordById($records, '1089480050');
		self::assertSame('972.8', $simpleSell['Total']);

		// Stock sale id=1100000003 amount=239.99, paired with close trade id=1100000002 amount=13.75.
		$partialSell = $this->findRecordById($records, '1100000003');
		self::assertSame('253.74', $partialSell['Total']);
	}

	public function testGetRecordsKeepsBuyTotalUnchanged(): void
	{
		// Buys must not be touched by the close-trade pairing logic.
		$records = $this->getXtbRecords();

		$simpleBuy = $this->findRecordById($records, '1089479372');
		self::assertSame('982.94', $simpleBuy['Total']);
	}

	public function testGetRecordsParsesDividendWithWithholdingTax(): void
	{
		$records = $this->getXtbRecords();

		$dividendRecord = $this->findRecordById($records, '764987766');
		self::assertSame('DIVIDEND', $dividendRecord['Type']);
		self::assertSame('NVDA.US', $dividendRecord['Symbol']);
		self::assertSame('0.04', $dividendRecord['Tax']);
	}

	public function testGetRecordsPairsTaxWithDividendByIdEvenWhenNotAdjacent(): void
	{
		// XTB exports don't always place the Withholding Tax row directly after its
		// dividend — pairing must use tax_id == dividend_id + 1, not row adjacency.
		// The fixture has two interleaved pairs:
		//   row 27: DIVIDENT  id=686043413 (amt 0.15)
		//   row 28: DIVIDENT  id=860853604 (amt 0.13)
		//   row 29: WHT       id=686043414 (-0.05)  ← belongs to row 27
		//   row 30: WHT       id=860853605 (-0.04)  ← belongs to row 28
		// Index-based pairing would have attached 0.05 to the wrong dividend and dropped 0.04.
		$records = $this->getXtbRecords();

		self::assertSame('0.05', $this->findRecordById($records, '686043413')['Tax']);
		self::assertSame('0.04', $this->findRecordById($records, '860853604')['Tax']);
	}

	/**
	 * @param list<array<string, string>> $records
	 * @return array<string, string>
	 */
	private function findRecordById(array $records, string $id): array
	{
		foreach ($records as $record) {
			if ($record['Id'] === $id) {
				return $record;
			}
		}

		self::fail('Record with Id ' . $id . ' not found');
	}

	/** @return list<array<string, string>> */
	private function getXtbRecords(): array
	{
		$mapper = new XtbMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/xtb_export.xlsx');
		if ($fileContent === false) {
			self::fail('Fixture file not found');
		}

		return $mapper->getRecords($fileContent);
	}
}
