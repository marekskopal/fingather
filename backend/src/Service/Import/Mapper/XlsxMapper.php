<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use const PATHINFO_EXTENSION;

abstract class XlsxMapper implements XlsxMapperInterface
{
	private const TEMP_FILE_PREFIX = 'FinGatherXlsx_';

	/** @return list<array<string, string>> */
	public function getRecords(string $content): array
	{
		$spreadsheet = $this->loadSpreadsheet($content);

		try {
			return $this->getRecordsFromSheet($spreadsheet);
		} catch (Exception) {
			return [];
		}
	}

	/** @return list<int>|null */
	public function getAllowedMarketIds(): ?array
	{
		//Allow all markets by default
		return null;
	}

	/** @return list<array<string, string>> */
	abstract public function getRecordsFromSheet(Spreadsheet $spreadsheet): array;

	protected function loadSpreadsheet(string $content): Spreadsheet
	{
		$reader = new Xlsx();
		$reader->setReadDataOnly(true);

		$tempFile = tempnam(sys_get_temp_dir(), self::TEMP_FILE_PREFIX);
		if ($tempFile === false) {
			throw new \InvalidArgumentException('Could not create temp file');
		}

		file_put_contents($tempFile, $content);

		$spreadsheet = $reader->load($tempFile);

		unlink($tempFile);

		return $spreadsheet;
	}

	public function check(string $content, string $fileName): bool
	{
		$extension = pathinfo($fileName, PATHINFO_EXTENSION);
		return $extension === 'xlsx';
	}
}
