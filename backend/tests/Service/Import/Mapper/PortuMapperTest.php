<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\PortuMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PortuMapper::class)]
#[UsesClass(MappingDto::class)]
final class PortuMapperTest extends TestCase
{
	//@phpstan-ignore-next-line
	public function __construct(string $name)
	{
		parent::__construct($name);

		ImportTestDataProvider::setCurrentTestFile('portu_export.csv');
	}

	public function testGetImportType(): void
	{
		$mapper = new PortuMapper();
		self::assertSame(BrokerImportTypeEnum::Portu, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new PortuMapper();

		$mapping = $mapper->getMapping();

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->ticker);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->currency);
	}

	#[DataProviderExternal(ImportTestDataProvider::class, 'additionProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new PortuMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new PortuMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
