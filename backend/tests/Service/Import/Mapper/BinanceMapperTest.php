<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\BinanceMapper;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\Dto\MoneyValueDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(BinanceMapper::class)]
#[UsesClass(MappingDto::class)]
#[UsesClass(MoneyValueDto::class)]
final class BinanceMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'binance_export.csv';

	public function testGetImportType(): void
	{
		$mapper = new BinanceMapper();
		self::assertSame(BrokerImportTypeEnum::Binance, $mapper->getImportType());
	}

	public function testGetRecords(): void
	{
		$mapper = new BinanceMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/binance_export.csv');
		if ($fileContent === false) {
			self::fail('File not found');
		}

		$records = $mapper->getRecords($fileContent);

		self::assertCount(4, $records);
		self::assertArrayHasKey('Total', $records[1]);
		self::assertArrayHasKey('Currency', $records[1]);
	}

	public function testGetMapping(): void
	{
		$mapper = new BinanceMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->ticker);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->total);
		self::assertNotNull($mapping->currency);
		self::assertNotNull($mapping->importIdentifier);
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new BinanceMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new BinanceMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
