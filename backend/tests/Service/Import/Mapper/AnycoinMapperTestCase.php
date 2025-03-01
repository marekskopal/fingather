<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\AnycoinMapper;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(AnycoinMapper::class)]
#[UsesClass(MappingDto::class)]
final class AnycoinMapperTestCase extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'anycoin_export.csv';

	public function testGetImportType(): void
	{
		$mapper = new AnycoinMapper();
		self::assertSame(BrokerImportTypeEnum::Anycoin, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new AnycoinMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->ticker);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->currency);
		self::assertNotNull($mapping->importIdentifier);
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new AnycoinMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new AnycoinMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
