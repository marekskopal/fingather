<?php

declare(strict_types=1);

namespace FinGather\Service\Export;

use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\TaxReportDto;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

final class TaxReportExcelExporter
{
	private const string NumberFormat = '#,##0.00';

	public function export(TaxReportDto $taxReport, string $currencySymbol): string
	{
		$spreadsheet = new Spreadsheet();

		$this->createRealizedGainsSheet($spreadsheet->getActiveSheet(), $taxReport, $currencySymbol);
		$this->createUnrealizedPositionsSheet($spreadsheet->createSheet(), $taxReport, $currencySymbol);
		$this->createDividendsSheet($spreadsheet->createSheet(), $taxReport, $currencySymbol);
		$this->createSummarySheet($spreadsheet->createSheet(), $taxReport, $currencySymbol);

		$spreadsheet->setActiveSheetIndex(0);

		$tempFile = tempnam(sys_get_temp_dir(), 'tax_report_') . '.xlsx';
		$writer = new XlsxWriter($spreadsheet);
		$writer->save($tempFile);

		$spreadsheet->disconnectWorksheets();

		return $tempFile;
	}

	private function createRealizedGainsSheet(Worksheet $sheet, TaxReportDto $taxReport, string $currencySymbol): void
	{
		$sheet->setTitle('Realized Gains');

		$headers = [
			'Holding',
			'Name',
			'Buy Date',
			'Sell Date',
			'Holding Period (days)',
			'Units',
			'Buy Price (' . $currencySymbol . ')',
			'Sell Price (' . $currencySymbol . ')',
			'Cost Basis (' . $currencySymbol . ')',
			'Sales Proceeds (' . $currencySymbol . ')',
			'Fee (' . $currencySymbol . ')',
			'Gain/Loss (' . $currencySymbol . ')',
		];
		$this->writeHeaderRow($sheet, $headers);

		$row = 2;
		foreach ($taxReport->realizedGains->transactions as $tx) {
			$sheet->setCellValue('A' . $row, $tx->tickerTicker);
			$sheet->setCellValue('B' . $row, $tx->tickerName);
			$sheet->setCellValue('C' . $row, $tx->buyDate);
			$sheet->setCellValue('D' . $row, $tx->sellDate);
			$sheet->setCellValue('E' . $row, $tx->holdingPeriodDays);
			$this->setDecimalCell($sheet, 'F' . $row, $tx->units);
			$this->setDecimalCell($sheet, 'G' . $row, $tx->buyPrice);
			$this->setDecimalCell($sheet, 'H' . $row, $tx->sellPrice);
			$this->setDecimalCell($sheet, 'I' . $row, $tx->costBasis);
			$this->setDecimalCell($sheet, 'J' . $row, $tx->salesProceeds);
			$this->setDecimalCell($sheet, 'K' . $row, $tx->fee);
			$this->setDecimalCell($sheet, 'L' . $row, $tx->gainLoss);
			$row++;
		}

		// Totals row
		$sheet->setCellValue('A' . $row, 'Total');
		$sheet->getStyle('A' . $row)->getFont()->setBold(true);
		$this->setDecimalCell($sheet, 'I' . $row, $taxReport->realizedGains->totalCostBasis);
		$this->setDecimalCell($sheet, 'J' . $row, $taxReport->realizedGains->totalSalesProceeds);
		$this->setDecimalCell($sheet, 'K' . $row, $taxReport->realizedGains->totalFees);
		$this->setDecimalCell($sheet, 'L' . $row, $taxReport->realizedGains->netRealizedGainLoss);
		$sheet->getStyle('I' . $row . ':L' . $row)->getFont()->setBold(true);

		$this->autoSizeColumns($sheet, 'A', 'L');
		$this->setLandscapeFitToWidth($sheet);
	}

	private function createUnrealizedPositionsSheet(Worksheet $sheet, TaxReportDto $taxReport, string $currencySymbol): void
	{
		$sheet->setTitle('Unrealized Positions');

		$headers = [
			'Holding',
			'Name',
			'First Buy Date',
			'Holding Period (days)',
			'Units',
			'Avg. Buy Price (' . $currencySymbol . ')',
			'Cost Basis (' . $currencySymbol . ')',
			'Market Value (' . $currencySymbol . ')',
			'Gain/Loss (' . $currencySymbol . ')',
		];
		$this->writeHeaderRow($sheet, $headers);

		$row = 2;
		foreach ($taxReport->unrealizedPositions->positions as $pos) {
			$sheet->setCellValue('A' . $row, $pos->tickerTicker);
			$sheet->setCellValue('B' . $row, $pos->tickerName);
			$sheet->setCellValue('C' . $row, $pos->firstBuyDate);
			$sheet->setCellValue('D' . $row, $pos->holdingPeriodDays);
			$this->setDecimalCell($sheet, 'E' . $row, $pos->units);
			$this->setDecimalCell($sheet, 'F' . $row, $pos->buyPrice);
			$this->setDecimalCell($sheet, 'G' . $row, $pos->costBasis);
			$this->setDecimalCell($sheet, 'H' . $row, $pos->marketValue);
			$this->setDecimalCell($sheet, 'I' . $row, $pos->gainLoss);
			$row++;
		}

		// Totals row
		$sheet->setCellValue('A' . $row, 'Total');
		$sheet->getStyle('A' . $row)->getFont()->setBold(true);
		$this->setDecimalCell($sheet, 'G' . $row, $taxReport->unrealizedPositions->totalCostBasis);
		$this->setDecimalCell($sheet, 'H' . $row, $taxReport->unrealizedPositions->totalMarketValue);
		$this->setDecimalCell($sheet, 'I' . $row, $taxReport->unrealizedPositions->totalGainLoss);
		$sheet->getStyle('G' . $row . ':I' . $row)->getFont()->setBold(true);

		$this->autoSizeColumns($sheet, 'A', 'I');
		$this->setLandscapeFitToWidth($sheet);
	}

	private function createDividendsSheet(Worksheet $sheet, TaxReportDto $taxReport, string $currencySymbol): void
	{
		$sheet->setTitle('Dividends');

		// Dividends by country section
		$sheet->setCellValue('A1', 'Dividends by Country');
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

		$countryHeaders = [
			'Country',
			'Gross Amount (' . $currencySymbol . ')',
			'Tax (' . $currencySymbol . ')',
			'Net Amount (' . $currencySymbol . ')',
		];
		$row = 2;
		$this->writeHeaderRowAt($sheet, $countryHeaders, $row);
		$row++;

		foreach ($taxReport->dividends->dividendsByCountry as $country) {
			$sheet->setCellValue('A' . $row, $country->countryName);
			$this->setDecimalCell($sheet, 'B' . $row, $country->totalGross);
			$this->setDecimalCell($sheet, 'C' . $row, $country->totalTax);
			$this->setDecimalCell($sheet, 'D' . $row, $country->totalNet);
			$row++;
		}

		// Country totals
		$sheet->setCellValue('A' . $row, 'Total');
		$sheet->getStyle('A' . $row)->getFont()->setBold(true);
		$this->setDecimalCell($sheet, 'B' . $row, $taxReport->dividends->totalGross);
		$this->setDecimalCell($sheet, 'C' . $row, $taxReport->dividends->totalTax);
		$this->setDecimalCell($sheet, 'D' . $row, $taxReport->dividends->totalNet);
		$sheet->getStyle('B' . $row . ':D' . $row)->getFont()->setBold(true);

		// Dividend transactions section
		$row += 2;
		$sheet->setCellValue('A' . $row, 'Dividend Transactions');
		$sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
		$row++;

		$txHeaders = [
			'Holding',
			'Name',
			'Country',
			'Date',
			'Gross Amount (' . $currencySymbol . ')',
			'Tax (' . $currencySymbol . ')',
			'Net Amount (' . $currencySymbol . ')',
		];
		$this->writeHeaderRowAt($sheet, $txHeaders, $row);
		$row++;

		foreach ($taxReport->dividends->transactions as $div) {
			$sheet->setCellValue('A' . $row, $div->tickerTicker);
			$sheet->setCellValue('B' . $row, $div->tickerName);
			$sheet->setCellValue('C' . $row, $div->countryName);
			$sheet->setCellValue('D' . $row, $div->date);
			$this->setDecimalCell($sheet, 'E' . $row, $div->grossAmount);
			$this->setDecimalCell($sheet, 'F' . $row, $div->tax);
			$this->setDecimalCell($sheet, 'G' . $row, $div->netAmount);
			$row++;
		}

		$this->autoSizeColumns($sheet, 'A', 'G');
		$this->setLandscapeFitToWidth($sheet, PageSetup::PAPERSIZE_A4);
	}

	private function createSummarySheet(Worksheet $sheet, TaxReportDto $taxReport, string $currencySymbol): void
	{
		$sheet->setTitle('Summary');

		$sheet->setCellValue('A1', 'Tax Report Summary — ' . $taxReport->year);
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

		$sheet->setCellValue('A3', 'Realized Gains');
		$sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);

		$summaryData = [
			['Sales Proceeds', $taxReport->realizedGains->totalSalesProceeds],
			['Cost Basis', $taxReport->realizedGains->totalCostBasis],
			['Gains', $taxReport->realizedGains->totalGains],
			['Losses', $taxReport->realizedGains->totalLosses],
			['Fees', $taxReport->realizedGains->totalFees],
			['Net Realized Gain/Loss', $taxReport->realizedGains->netRealizedGainLoss],
		];

		$row = 4;
		foreach ($summaryData as [$label, $value]) {
			$sheet->setCellValue('A' . $row, $label);
			$this->setDecimalCell($sheet, 'B' . $row, $value);
			$row++;
		}

		$row++;
		$sheet->setCellValue('A' . $row, 'Dividends');
		$sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
		$row++;

		$sheet->setCellValue('A' . $row, 'Total Gross');
		$this->setDecimalCell($sheet, 'B' . $row, $taxReport->dividends->totalGross);
		$row++;
		$sheet->setCellValue('A' . $row, 'Total Tax Withheld');
		$this->setDecimalCell($sheet, 'B' . $row, $taxReport->dividends->totalTax);
		$row++;
		$sheet->setCellValue('A' . $row, 'Total Net');
		$this->setDecimalCell($sheet, 'B' . $row, $taxReport->dividends->totalNet);

		$row += 2;
		$sheet->setCellValue('A' . $row, 'Total Fees');
		$this->setDecimalCell($sheet, 'B' . $row, $taxReport->totalFees);
		$sheet->getStyle('A' . $row)->getFont()->setBold(true);
		$row++;
		$sheet->setCellValue('A' . $row, 'Total Taxes');
		$this->setDecimalCell($sheet, 'B' . $row, $taxReport->totalTaxes);
		$sheet->getStyle('A' . $row)->getFont()->setBold(true);

		$this->autoSizeColumns($sheet, 'A', 'B');
		$this->setLandscapeFitToWidth($sheet, PageSetup::PAPERSIZE_A4);
	}

	/** @param list<string> $headers */
	private function writeHeaderRow(Worksheet $sheet, array $headers): void
	{
		$this->writeHeaderRowAt($sheet, $headers, 1);
	}

	/** @param list<string> $headers */
	private function writeHeaderRowAt(Worksheet $sheet, array $headers, int $row): void
	{
		foreach ($headers as $index => $header) {
			$col = Coordinate::stringFromColumnIndex($index + 1);
			$sheet->setCellValue($col . $row, $header);
		}

		$lastCol = Coordinate::stringFromColumnIndex(count($headers));
		$sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->setBold(true);
		$sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
	}

	private function setDecimalCell(Worksheet $sheet, string $cell, Decimal $value): void
	{
		$sheet->setCellValue($cell, (float) $value->toString());
		$sheet->getStyle($cell)->getNumberFormat()->setFormatCode(self::NumberFormat);
	}

	private function setLandscapeFitToWidth(Worksheet $sheet, int $paperSize = PageSetup::PAPERSIZE_A3): void
	{
		$pageSetup = $sheet->getPageSetup();
		$pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
		$pageSetup->setPaperSize($paperSize);
		$pageSetup->setFitToWidth(1);
		$pageSetup->setFitToHeight(0);
	}

	private function autoSizeColumns(Worksheet $sheet, string $from, string $to): void
	{
		$fromOrd = ord($from);
		$toOrd = ord($to);
		for ($i = $fromOrd; $i <= $toOrd; $i++) {
			$sheet->getColumnDimension(chr($i))->setAutoSize(true);
		}
	}
}
