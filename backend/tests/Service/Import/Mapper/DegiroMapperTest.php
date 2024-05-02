<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\DegiroMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(DegiroMapper::class)]
class DegiroMapperTest extends TestCase
{
	public function testGetImportType(): void
	{
		$mapper = new DegiroMapper();
		$this->assertSame(BrokerImportTypeEnum::Degiro, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new DegiroMapper();

		$this->assertIsArray($mapper->getMapping());
	}

	#[TestWith(['degiro_export.csv', true])]
	#[TestWith(['anycoin_export.csv', false])]
	#[TestWith(['interactive_brokers_export.csv', false])]
	#[TestWith(['revolut_export.csv', false])]
	#[TestWith(['etoro_export.xlsx', false])]
	#[TestWith(['trading212_export.csv', false])]
	#[TestWith(['xtb_export.csv', false])]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new DegiroMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		$this->assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new DegiroMapper();
		$this->assertSame(',', $mapper->getCsvDelimiter());
	}
}
