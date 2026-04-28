<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Export;

use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\TaxReportDividendsDto;
use FinGather\Service\DataCalculator\Dto\TaxReportDto;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainsDto;
use FinGather\Service\DataCalculator\Dto\TaxReportUnrealizedDto;
use FinGather\Service\Export\TaxReportExcelExporter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxReportExcelExporter::class)]
#[UsesClass(TaxReportDto::class)]
#[UsesClass(TaxReportRealizedGainsDto::class)]
#[UsesClass(TaxReportUnrealizedDto::class)]
#[UsesClass(TaxReportDividendsDto::class)]
final class TaxReportExcelExporterTest extends TestCase
{
	private function makeEmptyTaxReport(int $year = 2024): TaxReportDto
	{
		$zero = new Decimal(0);

		return new TaxReportDto(
			year: $year,
			realizedGains: new TaxReportRealizedGainsDto(
				totalSalesProceeds: $zero,
				totalCostBasis: $zero,
				totalGains: $zero,
				totalLosses: $zero,
				totalFees: $zero,
				netRealizedGainLoss: $zero,
				transactions: [],
			),
			unrealizedPositions: new TaxReportUnrealizedDto(
				totalMarketValue: $zero,
				totalCostBasis: $zero,
				totalGainLoss: $zero,
				positions: [],
			),
			dividends: new TaxReportDividendsDto(
				totalGross: $zero,
				totalTax: $zero,
				totalNet: $zero,
				dividendsByCountry: [],
				transactions: [],
			),
			totalFees: $zero,
			totalTaxes: $zero,
		);
	}

	public function testExportCreatesValidXlsxFile(): void
	{
		$exporter = new TaxReportExcelExporter();

		$file = $exporter->export($this->makeEmptyTaxReport(), '$');

		self::assertFileExists($file);
		self::assertStringEndsWith('.xlsx', $file);

		unlink($file);
	}

	public function testExportCreatesAllFourSheets(): void
	{
		$exporter = new TaxReportExcelExporter();

		$file = $exporter->export($this->makeEmptyTaxReport(), '$');

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($file);

		self::assertCount(4, $spreadsheet->getAllSheets());
		self::assertSame('Realized Gains', $spreadsheet->getSheet(0)->getTitle());
		self::assertSame('Unrealized Positions', $spreadsheet->getSheet(1)->getTitle());
		self::assertSame('Dividends', $spreadsheet->getSheet(2)->getTitle());
		self::assertSame('Summary', $spreadsheet->getSheet(3)->getTitle());

		$spreadsheet->disconnectWorksheets();
		unlink($file);
	}

	public function testExportHeadersOnRealizedGainsSheetAreBold(): void
	{
		$exporter = new TaxReportExcelExporter();

		$file = $exporter->export($this->makeEmptyTaxReport(), '$');

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($file);
		$sheet = $spreadsheet->getSheet(0);

		// 12 headers in Realized Gains sheet
		for ($col = 1; $col <= 12; $col++) {
			$colLetter = Coordinate::stringFromColumnIndex($col);
			$cell = $sheet->getCell($colLetter . '1');
			self::assertTrue(
				$cell->getStyle()->getFont()->getBold(),
				sprintf('Realized Gains header column %s should be bold', $colLetter),
			);
		}

		$spreadsheet->disconnectWorksheets();
		unlink($file);
	}

	public function testExportSummarySheetContainsYear(): void
	{
		$exporter = new TaxReportExcelExporter();

		$file = $exporter->export($this->makeEmptyTaxReport(year: 2023), '$');

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($file);
		// Summary sheet
		$sheet = $spreadsheet->getSheet(3);

		$title = $sheet->getCell('A1')->getFormattedValue();
		self::assertStringContainsString('2023', $title);

		$spreadsheet->disconnectWorksheets();
		unlink($file);
	}

	public function testExportCurrencySymbolAppearsInHeaders(): void
	{
		$exporter = new TaxReportExcelExporter();

		$file = $exporter->export($this->makeEmptyTaxReport(), '€');

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($file);
		$sheet = $spreadsheet->getSheet(0);

		// Buy Price header should include currency symbol
		$buyPriceHeader = $sheet->getCell('G1')->getFormattedValue();
		self::assertStringContainsString('€', $buyPriceHeader);

		$spreadsheet->disconnectWorksheets();
		unlink($file);
	}

	public function testExportActiveSheetIsRealizedGains(): void
	{
		$exporter = new TaxReportExcelExporter();

		$file = $exporter->export($this->makeEmptyTaxReport(), '$');

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($file);

		self::assertSame(0, $spreadsheet->getActiveSheetIndex());

		$spreadsheet->disconnectWorksheets();
		unlink($file);
	}
}
