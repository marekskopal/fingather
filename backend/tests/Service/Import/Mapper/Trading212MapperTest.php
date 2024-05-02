<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Trading212Mapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Trading212Mapper::class)]
class Trading212MapperTest extends TestCase
{
	public function testGetImportType(): void
	{
		$mapper = new Trading212Mapper();
		$this->assertSame(BrokerImportTypeEnum::Trading212, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new Trading212Mapper();

		$this->assertIsArray($mapper->getMapping());
	}

	#[TestWith(['trading212_export.csv', true])]
	#[TestWith(['anycoin_export.csv', false])]
	#[TestWith(['degiro_export.csv', false])]
	#[TestWith(['interactive_brokers_export.csv', false])]
	#[TestWith(['revolut_export.csv', false])]
	#[TestWith(['etoro_export.xlsx', false])]
	#[TestWith(['xtb_export.csv', false])]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new Trading212Mapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		$this->assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new Trading212Mapper();
		$this->assertSame(',', $mapper->getCsvDelimiter());
	}
}
