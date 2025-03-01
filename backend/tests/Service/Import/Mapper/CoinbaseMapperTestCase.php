<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\CoinbaseMapper;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(CoinbaseMapper::class)]
#[UsesClass(MappingDto::class)]
final class CoinbaseMapperTestCase extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'coinbase_export.csv';

	public function testGetImportType(): void
	{
		$mapper = new CoinbaseMapper();
		self::assertSame(BrokerImportTypeEnum::Coinbase, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new CoinbaseMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->ticker);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->currency);
		self::assertNotNull($mapping->fee);
		self::assertNotNull($mapping->feeCurrency);
		self::assertNotNull($mapping->importIdentifier);
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new CoinbaseMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new CoinbaseMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
