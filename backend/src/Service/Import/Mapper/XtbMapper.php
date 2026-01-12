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
	private const string Created = 'Created';

	private const int ClosedPositionSheet = 0;
	private const int OpenPositionSheet = 1;

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
			importIdentifier: self::Id,
		);
		return $mappingDto;
	}

	/** @return list<array<string, string>> */
	#[Override]
	public function getRecordsFromSheet(Spreadsheet $spreadsheet): array
	{
		$closedPositionSheet = $spreadsheet->getSheet(self::ClosedPositionSheet);

		/** @var array<int, array<string, string>> $sheetData */
		$sheetData = $closedPositionSheet->toArray('', true, true, true);

		$records = [];

		foreach ($sheetData as $index => $row) {
			if ($index <= 12) {
				continue;
			}

			$records[] = [
				self::Id => $row['B'],
				self::Symbol => $row['C'],
				self::Type => $row['D'],
				self::Volume => $row['E'],
				self::Created => $row['D'] === 'Buy' ? $row['F'] : $row['H'],
				self::Price => $row['D'] === 'Buy' ? $row['G'] : $row['I'],
			];
		}

		$openPositionSheet = $spreadsheet->getSheet(self::OpenPositionSheet);

		/** @var array<int, array<string, string>> $sheetData */
		$sheetData = $openPositionSheet->toArray('', true, true, true);

		$records = [];

		foreach ($sheetData as $index => $row) {
			if ($index <= 11) {
				continue;
			}

			$records[] = [
				self::Id => $row['B'],
				self::Symbol => $row['C'],
				self::Type => $row['D'],
				self::Volume => $row['E'],
				self::Created => $row['F'],
				self::Price => $row['G'],
			];
		}

		return $records;
	}

	#[Override]
	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$spreadsheet = $this->loadSpreadsheet($content);

		$closedPositionSheet = $spreadsheet->getSheet(self::ClosedPositionSheet);
		$openPositionSheet = $spreadsheet->getSheet(self::OpenPositionSheet);

		return
			str_starts_with($closedPositionSheet->getTitle(), 'CLOSED POSITION')
			&& str_starts_with($openPositionSheet->getTitle(), 'OPEN POSITION');
	}
}
