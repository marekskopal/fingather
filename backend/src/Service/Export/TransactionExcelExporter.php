<?php

declare(strict_types=1);

namespace FinGather\Service\Export;

use FinGather\Model\Entity\Transaction;
use Iterator;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

final readonly class TransactionExcelExporter
{
	/** @param Iterator<Transaction> $transactions */
	public function export(Iterator $transactions): string
	{
		$spreadsheet = new Spreadsheet();

		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Transactions');

		$headers = [
			'Date',
			'Type',
			'Ticker Symbol',
			'Ticker Name',
			'Units',
			'Price',
			'Currency',
			'Tax',
			'Tax Currency',
			'Fee',
			'Fee Currency',
			'Notes',
			'Import Identifier',
		];

		$this->writeHeaderRow($sheet, $headers);

		$row = 2;
		foreach ($transactions as $transaction) {
			$sheet->setCellValue('A' . $row, $transaction->actionCreated->format('Y-m-d H:i:s'));
			$sheet->setCellValue('B' . $row, $transaction->actionType->value);
			$sheet->setCellValue('C' . $row, $transaction->asset->ticker->ticker);
			$sheet->setCellValue('D' . $row, $transaction->asset->ticker->name);
			$sheet->setCellValue('E' . $row, (float) $transaction->units->toString());
			$sheet->setCellValue('F' . $row, (float) $transaction->price->toString());
			$sheet->setCellValue('G' . $row, $transaction->currency->code);
			$sheet->setCellValue('H' . $row, (float) $transaction->tax->toString());
			$sheet->setCellValue('I' . $row, $transaction->taxCurrency->code);
			$sheet->setCellValue('J' . $row, (float) $transaction->fee->toString());
			$sheet->setCellValue('K' . $row, $transaction->feeCurrency->code);
			$sheet->setCellValue('L' . $row, $transaction->notes ?? '');
			$sheet->setCellValue('M' . $row, $transaction->importIdentifier ?? '');
			$row++;
		}

		$this->autoSizeColumns($sheet, 'A', 'M');

		$tempFile = tempnam(sys_get_temp_dir(), 'transaction_export_') . '.xlsx';
		$writer = new XlsxWriter($spreadsheet);
		$writer->save($tempFile);

		$spreadsheet->disconnectWorksheets();

		return $tempFile;
	}

	/** @param list<string> $headers */
	private function writeHeaderRow(Worksheet $sheet, array $headers): void
	{
		foreach ($headers as $index => $header) {
			$col = Coordinate::stringFromColumnIndex($index + 1);
			$sheet->setCellValue($col . '1', $header);
		}

		$lastCol = Coordinate::stringFromColumnIndex(count($headers));
		$sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
		$sheet->getStyle('A1:' . $lastCol . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
