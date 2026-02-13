<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use Generator;
use Nette\Utils\Finder;
use PHPUnit\Framework\TestCase;

abstract class AbstractMapperTestCase extends TestCase
{
	protected static string $currentTestFile = '';

	/** @return Generator<int, array{0:string, 1:bool}> */
	public static function mapperDataProvider(): Generator
	{
		$files = Finder::findFiles('*')
			->from(__DIR__ . '/../../../Fixtures/Import/File')
			->collect();

		foreach ($files as $file) {
			yield [$file->getFilename(), $file->getFilename() === static::$currentTestFile];
		}
	}
}
