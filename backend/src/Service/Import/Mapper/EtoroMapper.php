<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class EtoroMapper extends XlsxMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Etoro;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'B',
			created: function (array $record): ?string {
				$dateTime = DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $record['A']);
				return $dateTime instanceof DateTimeImmutable ? $dateTime->format(
					'Y-m-d H:i:s',
				) : null;
			},
			ticker: fn (array $record): ?string => $record['C'] !== '' ? explode('/', $record['C'])[0] : null,
			units: fn (array $record): ?string => $record['E'] !== '-' ? $record['E'] : null,
			price: 'D',
			currency: fn (array $record): ?string => $record['C'] !== '' ? (explode('/', $record['C'])[1] ?? null) : null,
			importIdentifier: 'I',
		);
	}

	/** @return list<array<string, string>> */
	#[Override]
	public function getRecordsFromSheet(Spreadsheet $spreadsheet): array
	{
		$sheet = $spreadsheet->getSheet(2);

		/** @var array<int, array<string, string>> $sheetData */
		$sheetData = $sheet->toArray('', true, true, true);
		array_shift($sheetData);

		return array_values($sheetData);
	}

	#[Override]
	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$spreadsheet = $this->loadSpreadsheet($content);

		try {
			$spreadsheet->getSheet(4);
		} catch (Exception) {
			return false;
		}

		$records = $this->getRecords($content);

		return
			// Check if there is at least one record (header is not counted)
			isset($records[1]) &&
			array_key_exists('A', $records[1]) &&
			array_key_exists('B', $records[1]) &&
			array_key_exists('C', $records[1]) &&
			array_key_exists('D', $records[1]) &&
			array_key_exists('E', $records[1]) &&
			array_key_exists('I', $records[1]);
	}
}
