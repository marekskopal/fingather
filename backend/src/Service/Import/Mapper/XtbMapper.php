<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class XtbMapper extends XlsxMapper
{
	private const string Id = 'Id';
	private const string Symbol = 'Symbol';
	private const string Type = 'Type';
	private const string Volume = 'Volume';
	private const string Price = 'Price';
	private const string Total = 'Total';
	private const string Created = 'Created';
	private const string Currency = 'Currency';
	private const string Tax = 'Tax';

	private const int CashOperationHistorySheet = 3;

	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Xtb;
	}

	public function getMapping(): MappingDto
	{
		$mappingDto = new MappingDto(
			actionType: self::Type,
			created: fn (array $record): string => Date::excelToDateTimeObject((float) $record[self::Created])->format('Y-m-d H:i:s'),
			ticker: fn (array $record): string => substr($record[self::Symbol], 0, (int) strrpos($record[self::Symbol], '.')),
			units: self::Volume,
			price: self::Price,
			total: self::Total,
			currency: self::Currency,
			tax: self::Tax,
			importIdentifier: self::Id,
		);
		return $mappingDto;
	}

	/** @return list<array<string, string>> */
	#[Override]
	public function getRecordsFromSheet(Spreadsheet $spreadsheet): array
	{
		$cashOperationSheet = $spreadsheet->getSheet(self::CashOperationHistorySheet);

		/** @var array<int, array<string, string>> $sheetData */
		$sheetData = $cashOperationSheet->toArray('', true, true, true);

		$currency = $sheetData[6]['F'] ?? '';

		$records = [];
		$dividendRecordsByIndex = [];

		foreach ($sheetData as $index => $row) {
			if ($index <= 11) {
				continue;
			}

			$comment = $row['E'];
			$type = $row['C'];
			$amount = abs((float) $row['G']);

			if ($type === 'Stock purchase' || $type === 'Stock sale') {
				$operationDetails = $this->parseOperationDetails($comment);
				if ($operationDetails === null) {
					continue;
				}

				$records[] = [
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
			} elseif ($type === 'DIVIDENT') {
				$pricePerShare = $this->parseDividendPricePerShare($comment);
				if ($pricePerShare === null) {
					continue;
				}

				$volume = (string) ($amount / (float) $pricePerShare);

				$record = [
					self::Id => $row['B'],
					self::Symbol => $row['F'],
					self::Type => 'DIVIDEND',
					self::Volume => $volume,
					self::Created => $row['D'],
					self::Price => (string) $amount,
					self::Total => '',
					self::Currency => $currency,
					self::Tax => '',
				];

				$records[] = $record;
				$dividendRecordsByIndex[$index] = count($records) - 1;
			} elseif ($type === 'Withholding Tax') {
				$taxAmount = abs((float) $row['G']);
				$previousIndex = $index - 1;

				if (isset($dividendRecordsByIndex[$previousIndex])) {
					$recordIndex = $dividendRecordsByIndex[$previousIndex];
					$records[$recordIndex][self::Tax] = (string) $taxAmount;
				}
			}
		}

		return $records;
	}

	/** @return array{action: string, volume: string}|null */
	private function parseOperationDetails(string $comment): ?array
	{
		if (preg_match('/^(OPEN|CLOSE)\s+(BUY|SELL)\s+([\d.]+)\s+@\s+([\d.]+)$/', $comment, $matches) !== 1) {
			return null;
		}

		$action = $matches[1] === 'CLOSE' ? 'SELL' : 'BUY';
		$volume = $matches[3];

		return [
			'action' => $action,
			'volume' => $volume,
		];
	}

	private function parseDividendPricePerShare(string $comment): ?string
	{
		if (preg_match('/(?:corr\s+)?[\w.]+\s+\w+\s+([\d.]+)\s*\/\s*SHR/', $comment, $matches) !== 1) {
			return null;
		}

		return $matches[1];
	}

	#[Override]
	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$spreadsheet = $this->loadSpreadsheet($content);

		$cashOperationSheet = $spreadsheet->getSheet(self::CashOperationHistorySheet);

		return str_starts_with($cashOperationSheet->getTitle(), 'CASH OPERATION HISTORY');
	}
}
