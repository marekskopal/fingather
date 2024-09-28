<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use function Safe\file_put_contents;
use function Safe\tempnam;
use function Safe\unlink;
use const PATHINFO_EXTENSION;

abstract class XlsxMapper implements XlsxMapperInterface
{
	private const TEMP_FILE_PREFIX = 'FinGatherEtoro_';

	/** @return list<array<string, string>> */
	public function getRecords(string $content): array
	{
		$reader = new Xlsx();
		$reader->setReadDataOnly(true);

		$tempFile = tempnam(sys_get_temp_dir(), self::TEMP_FILE_PREFIX);

		file_put_contents($tempFile, $content);

		$spreadsheet = $reader->load($tempFile);

		try {
			$sheet = $spreadsheet->getSheet($this->getSheetIndex());
		} catch (Exception) {
			return [];
		}

		$sheetData = $sheet->toArray(null, true, true, true);
		array_shift($sheetData);

		unlink($tempFile);

		return array_values($sheetData);
	}

	/** @return list<int>|null */
	public function getAllowedMarketIds(): ?array
	{
		//Allow all markets by default
		return null;
	}

	abstract public function getSheetIndex(): int;

	public function check(string $content, string $fileName): bool
	{
		$extension = pathinfo($fileName, PATHINFO_EXTENSION);
		return $extension === 'xlsx';
	}
}
