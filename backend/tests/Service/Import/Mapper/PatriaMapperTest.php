<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\PatriaMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(PatriaMapper::class)]
#[UsesClass(MappingDto::class)]
final class PatriaMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'patria_export.xlsx';

	public function testGetImportType(): void
	{
		$mapper = new PatriaMapper();
		self::assertSame(BrokerImportTypeEnum::Patria, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new PatriaMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->isin);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->currency);
		self::assertNotNull($mapping->fee);
		self::assertNotNull($mapping->feeCurrency);
		self::assertNotNull($mapping->importIdentifier);
	}

	public function testGetRecords(): void
	{
		$mapper = new PatriaMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/patria_export.xlsx');
		if ($fileContent === false) {
			self::fail('File not found');
		}

		$records = $mapper->getRecords($fileContent);

		self::assertCount(4, $records);

		self::assertSame('PONTD360893', $records[0]['OrderNumber']);
		self::assertSame('SELL', $records[0]['Type']);
		self::assertSame('GB0030913577', $records[0]['Isin']);
		self::assertSame('1', $records[0]['Units']);
		self::assertSame('1.989', $records[0]['Price']);
		self::assertSame('GBP', $records[0]['Currency']);
		self::assertSame('4.26', $records[0]['Fee']);

		self::assertSame('PONTD360755', $records[1]['OrderNumber']);
		self::assertSame('BUY', $records[1]['Type']);
		self::assertSame('GB0030913577', $records[1]['Isin']);
		self::assertSame('1', $records[1]['Units']);
		self::assertSame('1.986', $records[1]['Price']);
		self::assertSame('GBP', $records[1]['Currency']);
		self::assertSame('4.27', $records[1]['Fee']);

		self::assertSame('PONTD360703', $records[2]['OrderNumber']);
		self::assertSame('BUY', $records[2]['Type']);
		self::assertSame('US46438F1012', $records[2]['Isin']);
		self::assertSame('1', $records[2]['Units']);
		self::assertSame('39.12', $records[2]['Price']);
		self::assertSame('USD', $records[2]['Currency']);
		self::assertSame('3.02', $records[2]['Fee']);

		self::assertSame('PONTD360650', $records[3]['OrderNumber']);
		self::assertSame('BUY', $records[3]['Type']);
		self::assertSame('CZ0009000121', $records[3]['Isin']);
		self::assertSame('1', $records[3]['Units']);
		self::assertSame('480', $records[3]['Price']);
		self::assertSame('CZK', $records[3]['Currency']);
		self::assertSame('90', $records[3]['Fee']);
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new PatriaMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}
}
