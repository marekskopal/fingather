<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class PatriaMapper extends XlsxMapper
{
	private const string OrderNumber = 'OrderNumber';
	private const string Date = 'Date';
	private const string Type = 'Type';
	private const string Isin = 'Isin';
	private const string Units = 'Units';
	private const string Price = 'Price';
	private const string Currency = 'Currency';
	private const string Fee = 'Fee';

	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Patria;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: self::Type,
			created: function (array $record): ?string {
				$dateTime = DateTimeImmutable::createFromFormat('d.m.Y H:i', $record[self::Date]);
				return $dateTime instanceof DateTimeImmutable ? $dateTime->format('Y-m-d H:i:s') : null;
			},
			isin: self::Isin,
			units: self::Units,
			price: self::Price,
			currency: self::Currency,
			fee: self::Fee,
			feeCurrency: self::Currency,
			importIdentifier: self::OrderNumber,
		);
	}

	/** @return list<array<string, string>> */
	#[Override]
	public function getRecordsFromSheet(Spreadsheet $spreadsheet): array
	{
		$sheet = $spreadsheet->getSheet(0);

		/** @var array<int, array<string, string>> $sheetData */
		$sheetData = $sheet->toArray('', true, true, true);

		$records = [];

		foreach ($sheetData as $index => $row) {
			if ($index <= 14) {
				continue;
			}

			$status = $row['L'];
			$type = $row['C'];

			if ($status !== 'Realizovaný') {
				continue;
			}

			if ($type !== 'Nákup' && $type !== 'Prodej') {
				continue;
			}

			$marketFee = abs((float) $row['V']);
			$patriaFee = abs((float) $row['W']);
			$totalFee = $marketFee + $patriaFee;

			$records[] = [
				self::OrderNumber => $row['A'],
				self::Date => $row['B'],
				self::Type => $type === 'Nákup' ? 'BUY' : 'SELL',
				self::Isin => $row['D'],
				self::Units => $row['R'],
				self::Price => $row['S'],
				self::Currency => $row['K'],
				self::Fee => (string) $totalFee,
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

		try {
			$sheet = $spreadsheet->getSheet(0);
		} catch (Exception) {
			return false;
		}

		return $sheet->getTitle() === 'Transakce';
	}
}
