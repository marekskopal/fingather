<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\PortuMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

#[CoversClass(PortuMapper::class)]
final class PortuMapperTest extends TestCase
{
	public function testGetImportType(): void
	{
		$mapper = new PortuMapper();
		self::assertSame(BrokerImportTypeEnum::Portu, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new PortuMapper();

		$mapping = $mapper->getMapping();

		self::assertArrayHasKey('actionType', $mapping);
		self::assertArrayHasKey('created', $mapping);
		self::assertArrayHasKey('ticker', $mapping);
		self::assertArrayHasKey('units', $mapping);
		self::assertArrayHasKey('price', $mapping);
		self::assertArrayHasKey('currency', $mapping);
	}

	#[TestWith(['portu_export.csv', true])]
	#[TestWith(['anycoin_export.csv', false])]
	#[TestWith(['degiro_export.csv', false])]
	#[TestWith(['interactive_brokers_export.csv', false])]
	#[TestWith(['etoro_export.xlsx', false])]
	#[TestWith(['revolut_export.csv', false])]
	#[TestWith(['trading212_export.csv', false])]
	#[TestWith(['xtb_export.csv', false])]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new PortuMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new PortuMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
