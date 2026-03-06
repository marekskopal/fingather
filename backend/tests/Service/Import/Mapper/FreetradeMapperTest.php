<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\FreetradeMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(FreetradeMapper::class)]
#[UsesClass(MappingDto::class)]
final class FreetradeMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'freetrade_export.csv';

	public function testGetImportType(): void
	{
		$mapper = new FreetradeMapper();
		self::assertSame(BrokerImportTypeEnum::Freetrade, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new FreetradeMapper();

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
		$mapper = new FreetradeMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new FreetradeMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
