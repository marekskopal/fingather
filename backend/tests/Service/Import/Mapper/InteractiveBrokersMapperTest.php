<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\InteractiveBrokersMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

#[CoversClass(InteractiveBrokersMapper::class)]
final class InteractiveBrokersMapperTest extends TestCase
{
	public function testGetImportType(): void
	{
		$mapper = new InteractiveBrokersMapper();
		self::assertSame(BrokerImportTypeEnum::InteractiveBrokers, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new InteractiveBrokersMapper();

		$mapping = $mapper->getMapping();

		self::assertArrayHasKey('actionType', $mapping);
		self::assertArrayHasKey('created', $mapping);
		self::assertArrayHasKey('ticker', $mapping);
		self::assertArrayHasKey('isin', $mapping);
		self::assertArrayHasKey('marketMic', $mapping);
		self::assertArrayHasKey('units', $mapping);
		self::assertArrayHasKey('price', $mapping);
		self::assertArrayHasKey('currency', $mapping);
		self::assertArrayHasKey('tax', $mapping);
		self::assertArrayHasKey('fee', $mapping);
		self::assertArrayHasKey('feeCurrency', $mapping);
		self::assertArrayHasKey('importIdentifier', $mapping);
	}

	#[TestWith(['interactive_brokers_export.csv', true])]
	#[TestWith(['degiro_export.csv', false])]
	#[TestWith(['anycoin_export.csv', false])]
	#[TestWith(['etoro_export.xlsx', false])]
	#[TestWith(['revolut_export.csv', false])]
	#[TestWith(['trading212_export.csv', false])]
	#[TestWith(['xtb_export.csv', false])]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new InteractiveBrokersMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new InteractiveBrokersMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}
}
