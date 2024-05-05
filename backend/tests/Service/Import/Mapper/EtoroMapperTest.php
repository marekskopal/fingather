<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\EtoroMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

#[CoversClass(EtoroMapper::class)]
final class EtoroMapperTest extends TestCase
{
	public function testGetImportType(): void
	{
		$mapper = new EtoroMapper();
		self::assertSame(BrokerImportTypeEnum::Etoro, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new EtoroMapper();

		$mapping = $mapper->getMapping();

		self::assertArrayHasKey('actionType', $mapping);
		self::assertArrayHasKey('created', $mapping);
		self::assertArrayHasKey('ticker', $mapping);
		self::assertArrayHasKey('units', $mapping);
		self::assertArrayHasKey('price', $mapping);
		self::assertArrayHasKey('currency', $mapping);
		self::assertArrayHasKey('importIdentifier', $mapping);
	}

	public function testGetRecords(): void
	{
		$mapper = new EtoroMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/etoro_export.xlsx');

		$records = $mapper->getRecords($fileContent);

		self::assertCount(3, $records);
	}

	#[TestWith(['etoro_export.xlsx', true])]
	#[TestWith(['anycoin_export.csv', false])]
	#[TestWith(['degiro_export.csv', false])]
	#[TestWith(['interactive_brokers_export.csv', false])]
	#[TestWith(['revolut_export.csv', false])]
	#[TestWith(['trading212_export.csv', false])]
	#[TestWith(['xtb_export.csv', false])]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new EtoroMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetSheetIndex(): void
	{
		$mapper = new EtoroMapper();
		self::assertSame(2, $mapper->getSheetIndex());
	}
}
