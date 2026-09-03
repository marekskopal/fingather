<?php

declare(strict_types=1);

namespace FinGather\Utils;

use const PATHINFO_EXTENSION;

final readonly class CsvUtils
{
	/** A CSV file is "without records" when it holds no non-blank line after the header line. */
	public static function isCsvWithoutRecords(string $fileName, string $contents): bool
	{
		return self::isCsvFile($fileName) && !self::hasRecords($contents);
	}

	private static function isCsvFile(string $fileName): bool
	{
		return strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'csv';
	}

	private static function hasRecords(string $contents): bool
	{
		$lines = preg_split('/\r\n|\r|\n/', $contents);
		if ($lines === false) {
			return false;
		}

		$nonEmptyLines = array_filter($lines, static fn (string $line): bool => trim($line) !== '');

		return count($nonEmptyLines) >= 2;
	}
}
