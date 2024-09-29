<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use Generator;
use Nette\Utils\Finder;

final class ImportTestDataProvider
{
	private static string $currentTestFile = '';

	/** @api */
	public static function setCurrentTestFile(string $currentTestFile): void
	{
		self::$currentTestFile = $currentTestFile;
	}

	/** @api */
	public static function additionProvider(): Generator
	{
		$files = Finder::findFiles('*')
			->from(__DIR__ . '/../../../Fixtures/Import/File')
			->collect();

		foreach ($files as $file) {
			yield [$file->getFilename(), $file->getFilename() === self::$currentTestFile];
		}
	}
}
