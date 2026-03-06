<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\LightyearMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(LightyearMapper::class)]
#[UsesClass(MappingDto::class)]
final class LightyearMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'lightyear_export.csv';

	public function testGetImportType(): void
	{
		$mapper = new LightyearMapper();
		self::assertSame(BrokerImportTypeEnum::Lightyear, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new LightyearMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->ticker);
		self::assertNotNull($mapping->isin);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->total);
		self::assertNotNull($mapping->currency);
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new LightyearMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new LightyearMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
