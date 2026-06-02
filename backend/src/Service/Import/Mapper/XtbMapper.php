<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use Override;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class XtbMapper extends AbstractXtbMapper
{
	private const int CashOperationsSheet = 0;
	private const string CashOperationsSheetTitle = 'Cash Operations';

	private const int HeaderRow = 5;

	/** @return list<array<string, string>> */
	#[Override]
	public function getRecordsFromSheet(Spreadsheet $spreadsheet): array
	{
		$cashOperationsSheet = $spreadsheet->getSheet(self::CashOperationsSheet);

		/** @var array<int, array<string, string>> $sheetData */
		$sheetData = $cashOperationsSheet->toArray('', true, true, true);

		// New XTB exports rows newest-first; iterate oldest-first so a dividend
		// is always seen before its paired withholding-tax row (tax_id == div_id + 1).
		$sheetData = array_reverse($sheetData, preserve_keys: true);

		$records = [];
		$dividendRecordIndexById = [];

		foreach ($sheetData as $index => $row) {
			if ($index <= self::HeaderRow) {
				continue;
			}

			$type = $row['A'];

			if ($type === 'Stock purchase' || $type === 'Stock sell') {
				$record = $this->buildTradeRecord($row);
				if ($record !== null) {
					$records[] = $record;
				}
			} elseif ($type === 'Dividend') {
				$record = $this->buildDividendRecord($row);
				if ($record !== null) {
					$records[] = $record;
					$dividendRecordIndexById[$row['F']] = count($records) - 1;
				}
			} elseif ($type === 'Withholding tax') {
				// Tax row id == dividend id + 1; row order in the sheet isn't reliable,
				// so we pair by id rather than by adjacent index.
				$dividendId = (string) ((int) $row['F'] - 1);
				if (isset($dividendRecordIndexById[$dividendId])) {
					$records[$dividendRecordIndexById[$dividendId]][self::Tax] = (string) abs((float) $row['E']);
				}
			}
		}

		return $records;
	}

	/**
	 * @param array<string, string> $row
	 * @return array<string, string>|null
	 */
	private function buildTradeRecord(array $row): ?array
	{
		$operationDetails = $this->parseOperationDetails($row['G']);
		if ($operationDetails === null) {
			return null;
		}

		// New XTB exports sells with the proceeds already netted (no separate
		// "close trade" row), so the Amount column is the gross figure we want.
		$amount = abs((float) $row['E']);

		return [
			self::Id => $row['F'],
			self::Symbol => $row['B'],
			self::Type => $operationDetails['action'],
			self::Volume => $operationDetails['volume'],
			self::Created => $row['D'],
			self::Price => '',
			self::Total => (string) $amount,
			self::Currency => '',
			self::Tax => '',
		];
	}

	/**
	 * @param array<string, string> $row
	 * @return array<string, string>|null
	 */
	private function buildDividendRecord(array $row): ?array
	{
		$pricePerShare = $this->parseDividendPricePerShare($row['G']);
		if ($pricePerShare === null) {
			return null;
		}

		$amount = abs((float) $row['E']);

		return [
			self::Id => $row['F'],
			self::Symbol => $row['B'],
			self::Type => 'DIVIDEND',
			self::Volume => (string) ($amount / (float) $pricePerShare),
			self::Created => $row['D'],
			self::Price => (string) $amount,
			self::Total => '',
			self::Currency => '',
			self::Tax => '',
		];
	}

	#[Override]
	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$spreadsheet = $this->loadSpreadsheet($content);

		try {
			$cashOperationsSheet = $spreadsheet->getSheet(self::CashOperationsSheet);
		} catch (Exception) {
			return false;
		}

		return $cashOperationsSheet->getTitle() === self::CashOperationsSheetTitle;
	}
}
