<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\FioBankaMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

#[CoversClass(FioBankaMapper::class)]
#[UsesClass(MappingDto::class)]
final class FioBankaMapperTest extends TestCase
{
	//@phpstan-ignore-next-line
	public function __construct(string $name)
	{
		parent::__construct($name);

		ImportTestDataProvider::setCurrentTestFile('fio_banka_export.csv');
	}

	public function testGetImportType(): void
	{
		$mapper = new FioBankaMapper();
		self::assertSame(BrokerImportTypeEnum::FioBanka, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new FioBankaMapper();

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

	#[DataProviderExternal(ImportTestDataProvider::class, 'additionProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new FioBankaMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new FioBankaMapper();
		self::assertSame(';', $mapper->getCsvDelimiter());
	}
}