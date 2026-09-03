<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\CoinbaseMapper;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(CoinbaseMapper::class)]
#[UsesClass(MappingDto::class)]
final class CoinbaseMapperTest extends AbstractMapperTestCase
{
	protected static string $currentTestFile = 'coinbase_export.csv';

	public function testGetImportType(): void
	{
		$mapper = new CoinbaseMapper();
		self::assertSame(BrokerImportTypeEnum::Coinbase, $mapper->getImportType());
	}

	public function testGetMapping(): void
	{
		$mapper = new CoinbaseMapper();

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

	public function testGetRecordsSkipsFiatDepositsAndWithdrawals(): void
	{
		$mapper = new CoinbaseMapper();

		$records = $mapper->getRecords($this->loadFixture());

		self::assertCount(4, $records);
		self::assertSame(
			['Buy', 'Buy', 'Sell', 'Staking Income'],
			array_column($records, 'Transaction Type'),
		);
	}

	public function testTickerMappingAliasesEth2ToEth(): void
	{
		$mapper = new CoinbaseMapper();
		$ticker = $mapper->getMapping()->ticker;
		self::assertIsCallable($ticker);

		self::assertSame('ETH', $ticker(['Asset' => 'ETH2']));
		self::assertSame('BTC', $ticker(['Asset' => 'BTC']));
	}

	public function testPriceMappingStripsCurrencyPrefix(): void
	{
		$mapper = new CoinbaseMapper();
		$price = $mapper->getMapping()->price;
		self::assertIsCallable($price);

		self::assertSame('216735.8953850906809663', $price(['Price at Transaction' => 'Kč216735.8953850906809663']));
		self::assertSame('0', $price(['Price at Transaction' => '']));
	}

	#[DataProvider('mapperDataProvider')]
	public function testCheck(string $fileName, bool $expected): void
	{
		$mapper = new CoinbaseMapper();

		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/' . $fileName);
		if ($fileContent === false) {
			self::fail('File not found');
		}

		self::assertSame($expected, $mapper->check($fileContent, $fileName));
	}

	public function testGetCsvDelimiter(): void
	{
		$mapper = new CoinbaseMapper();
		self::assertSame(',', $mapper->getCsvDelimiter());
	}

	private function loadFixture(): string
	{
		$fileContent = file_get_contents(__DIR__ . '/../../../Fixtures/Import/File/coinbase_export.csv');
		if ($fileContent === false) {
			self::fail('File not found');
		}

		return $fileContent;
	}
}
