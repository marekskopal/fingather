<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use FinGather\Utils\CsvUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvUtils::class)]
final class CsvUtilsTest extends TestCase
{
	/** @return iterable<string, array{string, string, bool}> */
	public static function isCsvWithoutRecordsProvider(): iterable
	{
		yield 'empty csv' => ['trading212.csv', '', true];
		yield 'header only' => ['trading212.csv', "Action,Time,Ticker\n", true];
		yield 'header only, uppercase extension' => ['EXPORT.CSV', "Action,Time,Ticker\n", true];
		yield 'header and blank lines' => ['trading212.csv', "Action,Time,Ticker\r\n\r\n   \r\n", true];
		yield 'header and one record' => ['trading212.csv', "Action,Time,Ticker\nMarket buy,2024-01-01,AAPL\n", false];
		yield 'header and one record, CR line endings' => ['trading212.csv', "Action,Time,Ticker\rMarket buy,2024-01-01,AAPL", false];

		// Non-CSV files are never judged by their line count
		yield 'xlsx' => ['export.xlsx', 'PK', false];
		yield 'no extension' => ['export', '', false];
	}

	#[DataProvider('isCsvWithoutRecordsProvider')]
	public function testIsCsvWithoutRecords(string $fileName, string $contents, bool $expected): void
	{
		self::assertSame($expected, CsvUtils::isCsvWithoutRecords($fileName, $contents));
	}
}
