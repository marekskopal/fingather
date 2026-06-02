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

	public function testGetRecordsParsesStockPurchase(): void
	{
		$records = $this->getXtbRecords();

		// Stock purchase id=1089479372 — "OPEN BUY 0.1823 @ 257.80", amount -982.94
		$buy = $this->findRecordById($records, '1089479372');
		self::assertSame('BUY', $buy['Type']);
		self::assertSame('0.1823', $buy['Volume']);
		self::assertSame('AAPL.US', $buy['Symbol']);
		self::assertSame('982.94', $buy['Total']);
	}

	public function testGetRecordsParsesStockSellWithNettedAmount(): void
	{
		// Unlike the legacy format, the new XTB export emits a single "Stock sell"
		// row whose Amount is already the net sale proceeds — there is no
		// companion "close trade" row to add back. The Total must therefore come
		// straight from the Amount column.
		$records = $this->getXtbRecords();

		// Stock sell id=1089480049 — "CLOSE BUY 0.1823 @ 257.89", amount 972.80
		$sell = $this->findRecordById($records, '1089480049');
		self::assertSame('SELL', $sell['Type']);
		self::assertSame('0.1823', $sell['Volume']);
		self::assertSame('AAPL.US', $sell['Symbol']);
		self::assertSame('972.8', $sell['Total']);
	}

	public function testGetRecordsParsesDividendWithWithholdingTax(): void
	{
		$records = $this->getXtbRecords();

		// Dividend id=764987766 (NVDA, 0.14) with paired WHT id=764987767 (-0.04).
		$dividendRecord = $this->findRecordById($records, '764987766');
		self::assertSame('DIVIDEND', $dividendRecord['Type']);
		self::assertSame('NVDA.US', $dividendRecord['Symbol']);
		self::assertSame('0.04', $dividendRecord['Tax']);
	}

	public function testGetRecordsPairsTaxWithDividendByIdEvenWhenNotAdjacent(): void
	{
		// As in the legacy format, tax_id == dividend_id + 1 — pair by id rather
		// than by row adjacency. The fixture has interleaved pairs:
		//   Dividend  id=686043413 (amt 0.15)
		//   Dividend  id=860853604 (amt 0.13)
		//   WHT       id=686043414 (-0.05)  ← belongs to 686043413
		//   WHT       id=860853605 (-0.04)  ← belongs to 860853604
		$records = $this->getXtbRecords();

		self::assertSame('0.05', $this->findRecordById($records, '686043413')['Tax']);
		self::assertSame('0.04', $this->findRecordById($records, '860853604')['Tax']);
	}

	public function testGetRecordsSkipsNonTradeOperations(): void
	{
		// Deposits, free-funds interest and the "Total" summary row must not
		// produce records — only stock trades and dividends are imported.
		$records = $this->getXtbRecords();

		foreach ($records as $record) {
			self::assertContains($record['Type'], ['BUY', 'SELL', 'DIVIDEND']);
		}
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
