<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\AnycoinMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnycoinMapper::class)]
final class AnycoinMapperTest extends TestCase
{
	public function testGetImportType(): void
	{
		$mapper = new AnycoinMapper();
		$this->assertSame(BrokerImportTypeEnum::Anycoin, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new AnycoinMapper();

		$this->assertIsArray($mapper->getMapping());
	}

	#[TestWith(['anycoin_export.csv', true])]
	#[TestWith(['degiro_export.csv', false])]
	#[TestWith(['interactive_brokers_export.csv', false])]
	#[TestWith(['etoro_export.xlsx', false])]
	#[TestWith(['revolut_export.csv', false])]
	#[TestWith(['trading212_export.csv', false])]
	#[TestWith(['xtb_export.csv', false])]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new AnycoinMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		$this->assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new AnycoinMapper();
		$this->assertSame(',', $mapper->getCsvDelimiter());
	}
}
