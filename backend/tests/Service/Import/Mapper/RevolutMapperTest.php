<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\RevolutMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(RevolutMapper::class)]
class RevolutMapperTest extends TestCase
{
	public function testGetImportType(): void
	{
		$mapper = new RevolutMapper();
		$this->assertSame(BrokerImportTypeEnum::Revolut, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new RevolutMapper();

		$this->assertIsArray($mapper->getMapping());
	}

	#[TestWith(['revolut_export.csv', true])]
	#[TestWith(['anycoin_export.csv', false])]
	#[TestWith(['interactive_brokers_export.csv', false])]
	#[TestWith(['etoro_export.xlsx', false])]
	#[TestWith(['trading212_export.csv', false])]
	#[TestWith(['xtb_export.csv', false])]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new RevolutMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		$this->assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new RevolutMapper();
		$this->assertSame(',', $mapper->getCsvDelimiter());
	}
}
