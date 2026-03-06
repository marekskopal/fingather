<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\SchwabMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(SchwabMapper::class)]
#[UsesClass(MappingDto::class)]
final class SchwabMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'schwab_export.csv';

	public function testGetImportType(): void
	{
		$mapper = new SchwabMapper();
		self::assertSame(BrokerImportTypeEnum::Schwab, $mapper->getImportType());
	}

	public function testGetRecords(): void
	{
		$mapper = new SchwabMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/schwab_export.csv');
		if ($fileContent === false) {
			self::fail('File not found');
		}

		$records = $mapper->getRecords($fileContent);

		// 3 data rows; Transactions Total is stripped
		self::assertCount(3, $records);
		self::assertArrayHasKey('Action', $records[1]);
		self::assertArrayHasKey('Symbol', $records[1]);
	}

	public function testGetMapping(): void
	{
		$mapper = new SchwabMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->ticker);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->currency);
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new SchwabMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new SchwabMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
