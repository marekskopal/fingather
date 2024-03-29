<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\InteractiveBrokersMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(InteractiveBrokersMapper::class)]
class InteractiveBrokersMapperTest extends TestCase
{
	public function testGetImportType(): void
	{
		$mapper = new InteractiveBrokersMapper();
		$this->assertSame(BrokerImportTypeEnum::InteractiveBrokers, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new InteractiveBrokersMapper();

		$this->assertIsArray($mapper->getMapping());
	}

	#[TestWith(['interactive_brokers_export.csv', true])]
	#[TestWith(['anycoin_export.csv', false])]
	#[TestWith(['etoro_export.xlsx', false])]
	#[TestWith(['revolut_export.csv', false])]
	#[TestWith(['trading212_export.csv', false])]
	#[TestWith(['xtb_export.csv', false])]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new InteractiveBrokersMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		$this->assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new InteractiveBrokersMapper();
		$this->assertSame(',', $mapper->getCsvDelimiter());
	}
}
