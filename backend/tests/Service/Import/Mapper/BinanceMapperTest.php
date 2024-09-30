<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\BinanceMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

#[CoversClass(BinanceMapper::class)]
final class BinanceMapperTest extends TestCase
{
	//@phpstan-ignore-next-line
	public function __construct(string $name)
	{
		parent::__construct($name);

		ImportTestDataProvider::setCurrentTestFile('binance_export.csv');
	}

	public function testGetImportType(): void
	{
		$mapper = new BinanceMapper();
		self::assertSame(BrokerImportTypeEnum::Binance, $mapper->getImportType());
	}

	public function testGetRecords(): void
	{
		$mapper = new BinanceMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/binance_export.csv');

		$records = $mapper->getRecords($fileContent);

		self::assertCount(4, $records);
		self::assertArrayHasKey('Price', $records[1]);
		self::assertArrayHasKey('Currency', $records[1]);
	}

	public function testGetMapping(): void
	{
		$mapper = new BinanceMapper();

		$mapping = $mapper->getMapping();

		self::assertArrayHasKey('actionType', $mapping);
		self::assertArrayHasKey('created', $mapping);
		self::assertArrayHasKey('ticker', $mapping);
		self::assertArrayHasKey('units', $mapping);
		self::assertArrayHasKey('price', $mapping);
		self::assertArrayHasKey('currency', $mapping);
		self::assertArrayHasKey('importIdentifier', $mapping);
	}

	#[DataProviderExternal(ImportTestDataProvider::class, 'additionProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new BinanceMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new BinanceMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
