<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Export;

use ArrayIterator;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\Export\TransactionCsvExporter;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransactionCsvExporter::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(Transaction::class)]
#[UsesClass(User::class)]
#[UsesClass(Asset::class)]
final class TransactionCsvExporterTest extends TestCase
{
	public function testExportCreatesValidCsvFile(): void
	{
		$exporter = new TransactionCsvExporter();
		$transaction = TransactionFixture::getTransaction();

		$file = $exporter->export(new ArrayIterator([$transaction]));

		self::assertFileExists($file);
		self::assertStringEndsWith('.csv', $file);

		unlink($file);
	}

	public function testExportHeadersAreCorrect(): void
	{
		$exporter = new TransactionCsvExporter();

		$file = $exporter->export(new ArrayIterator([]));

		$handle = fopen($file, 'r');
		self::assertNotFalse($handle);
		$headers = fgetcsv($handle, escape: '\\');
		fclose($handle);

		self::assertNotFalse($headers);
		self::assertCount(13, $headers);
		self::assertSame('Date', $headers[0]);
		self::assertSame('Type', $headers[1]);
		self::assertSame('Ticker Symbol', $headers[2]);
		self::assertSame('Ticker Name', $headers[3]);
		self::assertSame('Units', $headers[4]);
		self::assertSame('Price', $headers[5]);
		self::assertSame('Currency', $headers[6]);
		self::assertSame('Tax', $headers[7]);
		self::assertSame('Tax Currency', $headers[8]);
		self::assertSame('Fee', $headers[9]);
		self::assertSame('Fee Currency', $headers[10]);
		self::assertSame('Notes', $headers[11]);
		self::assertSame('Import Identifier', $headers[12]);

		unlink($file);
	}

	public function testExportDecimalValuesAreStrings(): void
	{
		$exporter = new TransactionCsvExporter();
		$transaction = TransactionFixture::getTransaction();

		$file = $exporter->export(new ArrayIterator([$transaction]));

		$handle = fopen($file, 'r');
		self::assertNotFalse($handle);
		// skip headers
		fgetcsv($handle, escape: '\\');
		$dataRow = fgetcsv($handle, escape: '\\');
		fclose($handle);

		self::assertNotFalse($dataRow);

		// Decimal values are serialised as strings (not floats)
		// Units
		self::assertSame('10', $dataRow[4]);
		// Price
		self::assertSame('100', $dataRow[5]);
		// Tax
		self::assertSame('2', $dataRow[7]);
		// Fee
		self::assertSame('1', $dataRow[9]);

		unlink($file);
	}

	public function testExportEmptyTransactionsProducesOnlyHeaders(): void
	{
		$exporter = new TransactionCsvExporter();

		$file = $exporter->export(new ArrayIterator([]));

		$rows = [];
		$handle = fopen($file, 'r');
		self::assertNotFalse($handle);
		while (($row = fgetcsv($handle, escape: '\\')) !== false) {
			$rows[] = $row;
		}
		fclose($handle);

		self::assertCount(1, $rows);

		unlink($file);
	}
}
