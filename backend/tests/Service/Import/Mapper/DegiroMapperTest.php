<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\DegiroMapper;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\file_get_contents;

#[CoversClass(DegiroMapper::class)]
#[UsesClass(MappingDto::class)]
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

		self::assertNotNull($mapping->actionType);
		self::assertNotNull($mapping->created);
		self::assertNotNull($mapping->isin);
		self::assertNotNull($mapping->units);
		self::assertNotNull($mapping->price);
		self::assertNotNull($mapping->currency);
		self::assertNotNull($mapping->tax);
		self::assertNotNull($mapping->taxCurrency);
		self::assertNotNull($mapping->fee);
		self::assertNotNull($mapping->feeCurrency);
		self::assertNotNull($mapping->importIdentifier);
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

	public function testSanitizeContent(): void
	{
		$mapper = new DegiroMapper();

		$reflection = new ReflectionClass($mapper);

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/degiro_export.csv');

		$method = $reflection->getMethod('sanitizeContent');
		$method->setAccessible(true);

		/** @var string $result */
		$result = $method->invoke($mapper, $fileContent);
		$firstLine = explode("\n", $result)[0];

		$sanitizedFirstLine = 'Datum,Čas,Datum2,Produkt,ISIN,Popis,Kurz,Pohyb,Pohyb2,Zůstatek,Zůstatek2,ID objednávky';

		self::assertSame($firstLine, $sanitizedFirstLine);
	}

	#[TestWith(['Nákup 4 CEZ as@480 CZK (CZ0005112300)', 'action', 'Nákup'])]
	#[TestWith(['Nákup 4 CEZ as@480 CZK (CZ0005112300)', 'units', '4'])]
	#[TestWith(['Nákup 4 CEZ as@480 CZK (CZ0005112300)', 'price', '480'])]
	#[TestWith(['Nákup 4 CEZ as@480 CZK (CZ0005112300)', 'currency', 'CZK'])]
	#[TestWith(['Nákup 100 EURONAV - TD@16,29 EUR (BE0003816338)', 'action', 'Nákup'])]
	#[TestWith(['Nákup 100 EURONAV - TD@16,29 EUR (BE0003816338)', 'units', '100'])]
	#[TestWith(['Nákup 100 EURONAV - TD@16,29 EUR (BE0003816338)', 'price', '16.29'])]
	#[TestWith(['Nákup 100 EURONAV - TD@16,29 EUR (BE0003816338)', 'currency', 'EUR'])]
	#[TestWith(['abc', 'action', null])]
	public function testParseFromDescription(string $description, string $variableName, ?string $expected): void
	{
		$mapper = new DegiroMapper();

		$reflection = new ReflectionClass($mapper);

		$method = $reflection->getMethod('parseFromDescription');
		$method->setAccessible(true);

		$result = $method->invoke($mapper, $description, $variableName);

		self::assertSame($expected, $result);
	}
}
