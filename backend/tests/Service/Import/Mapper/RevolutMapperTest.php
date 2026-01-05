<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\RevolutMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(RevolutMapper::class)]
#[UsesClass(MappingDto::class)]
final class RevolutMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'revolut_export.csv';

	public function testGetImportType(): void
	{
		$mapper = new RevolutMapper();
		self::assertSame(BrokerImportTypeEnum::Revolut, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new RevolutMapper();

		$mappings = $mapper->getMapping();

		self::assertNotNull($mappings->actionType);
		self::assertNotNull($mappings->created);
		self::assertNotNull($mappings->ticker);
		self::assertNotNull($mappings->units);
		self::assertNotNull($mappings->price);
		self::assertNotNull($mappings->currency);
		self::assertNotNull($mappings->total);
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new RevolutMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new RevolutMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
