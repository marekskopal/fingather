<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Export;

use ArrayIterator;
use FinGather\Service\Export\TransactionExcelExporter;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransactionExcelExporter::class)]
final class TransactionExcelExporterTest extends TestCase
{
	public function testExportCreatesValidXlsxFile(): void
	{
		$exporter = new TransactionExcelExporter();
		$transaction = TransactionFixture::getTransaction();

		$file = $exporter->export(new ArrayIterator([$transaction]));

		self::assertFileExists($file);
		self::assertStringEndsWith('.xlsx', $file);

		unlink($file);
	}

	public function testExportHeadersAreBold(): void
	{
		$exporter = new TransactionExcelExporter();

		$file = $exporter->export(new ArrayIterator([]));

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($file);
		$sheet = $spreadsheet->getActiveSheet();

		// All 13 header cells in row 1 should be bold
		for ($col = 1; $col <= 13; $col++) {
			$colLetter = Coordinate::stringFromColumnIndex($col);
			$cell = $sheet->getCell($colLetter . '1');
			self::assertTrue(
				$cell->getStyle()->getFont()->getBold(),
				"Header column {$colLetter} should be bold",
			);
		}

		$spreadsheet->disconnectWorksheets();
		unlink($file);
	}

	public function testExportDecimalValuesAreFloats(): void
	{
		$exporter = new TransactionExcelExporter();
		$transaction = TransactionFixture::getTransaction();

		$file = $exporter->export(new ArrayIterator([$transaction]));

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($file);
		$sheet = $spreadsheet->getActiveSheet();

		// Row 2 (first data row): Units=E2, Price=F2, Tax=H2, Fee=J2
		$units = $sheet->getCell('E2')->getValue();
		$price = $sheet->getCell('F2')->getValue();
		$tax = $sheet->getCell('H2')->getValue();
		$fee = $sheet->getCell('J2')->getValue();

		self::assertIsFloat($units);
		self::assertIsFloat($price);
		self::assertIsFloat($tax);
		self::assertIsFloat($fee);

		self::assertSame(10.0, $units);
		self::assertSame(100.0, $price);
		self::assertSame(2.0, $tax);
		self::assertSame(1.0, $fee);

		$spreadsheet->disconnectWorksheets();
		unlink($file);
	}

	public function testExportEmptyTransactionsProducesOnlyHeaders(): void
	{
		$exporter = new TransactionExcelExporter();

		$file = $exporter->export(new ArrayIterator([]));

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($file);
		$sheet = $spreadsheet->getActiveSheet();

		// Row 1 has headers, row 2 should be empty
		self::assertNotEmpty($sheet->getCell('A1')->getValue());
		self::assertEmpty($sheet->getCell('A2')->getValue());

		$spreadsheet->disconnectWorksheets();
		unlink($file);
	}
}
