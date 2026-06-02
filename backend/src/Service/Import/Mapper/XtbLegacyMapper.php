<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use Override;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class XtbLegacyMapper extends AbstractXtbMapper
{
	private const int CashOperationHistorySheet = 3;

	/** @return list<array<string, string>> */
	#[Override]
	public function getRecordsFromSheet(Spreadsheet $spreadsheet): array
	{
		$cashOperationSheet = $spreadsheet->getSheet(self::CashOperationHistorySheet);

		/** @var array<int, array<string, string>> $sheetData */
		$sheetData = $cashOperationSheet->toArray('', true, true, true);

		$currency = $sheetData[6]['F'] ?? '';

		$closeTradeAmountById = $this->indexCloseTradeAmounts($sheetData);

		$records = [];
		$dividendRecordIndexById = [];

		foreach ($sheetData as $index => $row) {
			if ($index <= 11) {
				continue;
			}

			$type = $row['C'];

			if ($type === 'Stock purchase' || $type === 'Stock sale') {
				$record = $this->buildTradeRecord($row, $currency, $closeTradeAmountById);
				if ($record !== null) {
					$records[] = $record;
				}
			} elseif ($type === 'DIVIDENT') {
				$record = $this->buildDividendRecord($row, $currency);
				if ($record !== null) {
					$records[] = $record;
					$dividendRecordIndexById[$row['B']] = count($records) - 1;
				}
			} elseif ($type === 'Withholding Tax') {
				// Tax row id == dividend id + 1; row order in the sheet isn't reliable,
				// so we pair by id rather than by adjacent index.
				$dividendId = (string) ((int) $row['B'] - 1);
				if (isset($dividendRecordIndexById[$dividendId])) {
					$records[$dividendRecordIndexById[$dividendId]][self::Tax] = (string) abs((float) $row['G']);
				}
			}
		}

		return $records;
	}

	/**
	 * @param array<string, string> $row
	 * @param array<string, float> $closeTradeAmountById
	 * @return array<string, string>|null
	 */
	private function buildTradeRecord(array $row, string $currency, array $closeTradeAmountById): ?array
	{
		$operationDetails = $this->parseOperationDetails($row['E']);
		if ($operationDetails === null) {
			return null;
		}

		$amount = $row['C'] === 'Stock sale'
			? $this->resolveSellAmount($row, $closeTradeAmountById)
			: abs((float) $row['G']);

		return [
			self::Id => $row['B'],
			self::Symbol => $row['F'],
			self::Type => $operationDetails['action'],
			self::Volume => $operationDetails['volume'],
			self::Created => $row['D'],
			self::Price => '',
			self::Total => (string) $amount,
			self::Currency => $currency,
			self::Tax => '',
		];
	}

	/**
	 * @param array<string, string> $row
	 * @return array<string, string>|null
	 */
	private function buildDividendRecord(array $row, string $currency): ?array
	{
		$pricePerShare = $this->parseDividendPricePerShare($row['E']);
		if ($pricePerShare === null) {
			return null;
		}

		$amount = abs((float) $row['G']);

		return [
			self::Id => $row['B'],
			self::Symbol => $row['F'],
			self::Type => 'DIVIDEND',
			self::Volume => (string) ($amount / (float) $pricePerShare),
			self::Created => $row['D'],
			self::Price => (string) $amount,
			self::Total => '',
			self::Currency => $currency,
			self::Tax => '',
		];
	}

	/**
	 * XTB splits each sell into two rows: a "close trade" carrying the realised
	 * P/L and a "Stock sale" whose amount is only the released cost basis. Index
	 * the close-trade amounts by id so we can add them to the matching sale
	 * (close_trade.id == stock_sale.id - 1) and recover the true gross proceeds.
	 *
	 * @param array<int, array<string, string>> $sheetData
	 * @return array<string, float>
	 */
	private function indexCloseTradeAmounts(array $sheetData): array
	{
		$closeTradeAmountById = [];
		foreach ($sheetData as $index => $row) {
			if ($index <= 11) {
				continue;
			}
			if ($row['C'] === 'close trade') {
				$closeTradeAmountById[$row['B']] = (float) $row['G'];
			}
		}

		return $closeTradeAmountById;
	}

	/**
	 * @param array<string, string> $row
	 * @param array<string, float> $closeTradeAmountById
	 */
	private function resolveSellAmount(array $row, array $closeTradeAmountById): float
	{
		$rawAmount = (float) $row['G'];
		$closeTradeId = (string) ((int) $row['B'] - 1);
		if (isset($closeTradeAmountById[$closeTradeId])) {
			$rawAmount += $closeTradeAmountById[$closeTradeId];
		}

		return abs($rawAmount);
	}

	#[Override]
	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$spreadsheet = $this->loadSpreadsheet($content);

		try {
			$cashOperationSheet = $spreadsheet->getSheet(self::CashOperationHistorySheet);
		} catch (Exception) {
			return false;
		}

		return str_starts_with($cashOperationSheet->getTitle(), 'CASH OPERATION HISTORY');
	}
}
