<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\EtoroMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(EtoroMapper::class)]
#[UsesClass(MappingDto::class)]
final class EtoroMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'etoro_export.xlsx';

	public function testGetImportType(): void
	{
		$mapper = new EtoroMapper();
		self::assertSame(BrokerImportTypeEnum::Etoro, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new EtoroMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->ticker);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->currency);
		self::assertNotNull($mapping->importIdentifier);
	}

	public function testGetRecords(): void
	{
		$mapper = new EtoroMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/etoro_export.xlsx');
		if ($fileContent === false) {
			self::fail('File not found');
		}

		$records = $mapper->getRecords($fileContent);

		self::assertCount(3, $records);
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new EtoroMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}
}
