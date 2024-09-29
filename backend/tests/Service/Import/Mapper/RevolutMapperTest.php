<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\RevolutMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

#[CoversClass(RevolutMapper::class)]
final class RevolutMapperTest extends TestCase
{
	//@phpstan-ignore-next-line
	public function __construct(string $name)
	{
		parent::__construct($name);

		ImportTestDataProvider::setCurrentTestFile('revolut_export.csv');
	}

	public function testGetImportType(): void
	{
		$mapper = new RevolutMapper();
		self::assertSame(BrokerImportTypeEnum::Revolut, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new RevolutMapper();

		$mappings = $mapper->getMapping();

		self::assertArrayHasKey('actionType', $mappings);
		self::assertArrayHasKey('created', $mappings);
		self::assertArrayHasKey('ticker', $mappings);
		self::assertArrayHasKey('units', $mappings);
		self::assertArrayHasKey('price', $mappings);
		self::assertArrayHasKey('currency', $mappings);
		self::assertArrayHasKey('total', $mappings);
	}

	#[DataProviderExternal(ImportTestDataProvider::class, 'additionProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new RevolutMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new RevolutMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
