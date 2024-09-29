<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Trading212Mapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

#[CoversClass(Trading212Mapper::class)]
final class Trading212MapperTest extends TestCase
{
	//@phpstan-ignore-next-line
	public function __construct(string $name)
	{
		parent::__construct($name);

		ImportTestDataProvider::setCurrentTestFile('trading212_export.csv');
	}

	public function testGetImportType(): void
	{
		$mapper = new Trading212Mapper();
		self::assertSame(BrokerImportTypeEnum::Trading212, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new Trading212Mapper();

		$mapping = $mapper->getMapping();

		self::assertArrayHasKey('actionType', $mapping);
		self::assertArrayHasKey('created', $mapping);
		self::assertArrayHasKey('ticker', $mapping);
		self::assertArrayHasKey('isin', $mapping);
		self::assertArrayHasKey('units', $mapping);
		self::assertArrayHasKey('price', $mapping);
		self::assertArrayHasKey('currency', $mapping);
		self::assertArrayHasKey('tax', $mapping);
		self::assertArrayHasKey('taxCurrency', $mapping);
		self::assertArrayHasKey('fee', $mapping);
		self::assertArrayHasKey('feeCurrency', $mapping);
		self::assertArrayHasKey('importIdentifier', $mapping);
	}

	#[DataProviderExternal(ImportTestDataProvider::class, 'additionProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new Trading212Mapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new Trading212Mapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
