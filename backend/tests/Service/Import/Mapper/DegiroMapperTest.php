<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\DegiroMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

#[CoversClass(DegiroMapper::class)]
final class DegiroMapperTest extends TestCase
{
	//@phpstan-ignore-next-line
	public function __construct(string $name)
	{
		parent::__construct($name);

		ImportTestDataProvider::setCurrentTestFile('degiro_export.csv');
	}

	public function testGetImportType(): void
	{
		$mapper = new DegiroMapper();
		self::assertSame(BrokerImportTypeEnum::Degiro, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new DegiroMapper();

		$mapping = $mapper->getMapping();

		self::assertArrayHasKey('actionType', $mapping);
		self::assertArrayHasKey('created', $mapping);
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
		$mapper = new DegiroMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new DegiroMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
