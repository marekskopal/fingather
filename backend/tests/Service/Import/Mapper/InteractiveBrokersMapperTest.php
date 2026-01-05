<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\InteractiveBrokersMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(InteractiveBrokersMapper::class)]
#[UsesClass(MappingDto::class)]
final class InteractiveBrokersMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'interactive_brokers_export.csv';

	public function testGetImportType(): void
	{
		$mapper = new InteractiveBrokersMapper();
		self::assertSame(BrokerImportTypeEnum::InteractiveBrokers, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new InteractiveBrokersMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->ticker);
		self::assertNotNull($mapping->isin);
		self::assertNotNull($mapping->marketMic);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->currency);
		self::assertNotNull($mapping->tax);
		self::assertNotNull($mapping->fee);
		self::assertNotNull($mapping->feeCurrency);
		self::assertNotNull($mapping->importIdentifier);
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new InteractiveBrokersMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new InteractiveBrokersMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
