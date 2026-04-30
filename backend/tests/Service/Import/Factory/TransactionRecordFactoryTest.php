<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Import\Factory;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Factory\TransactionRecordFactory;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\MapperInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransactionRecordFactory::class)]
#[UsesClass(MappingDto::class)]
#[UsesClass(TransactionRecord::class)]
final class TransactionRecordFactoryTest extends TestCase
{
	public function testStringMappingReadsCsvColumns(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(
			ticker: 'symbol',
			currency: 'currency',
			notes: 'notes',
		));

		$record = $factory->createFromCsvRecord($mapper, [
			'symbol' => 'AAPL',
			'currency' => 'USD',
			'notes' => 'Bought via app',
		]);

		self::assertSame('AAPL', $record->ticker);
		self::assertSame('USD', $record->currency);
		self::assertSame('Bought via app', $record->notes);
	}

	public function testClosureMappingReceivesEntireCsvRecord(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(
			ticker: static fn (array $record): string => $record['raw_symbol'] . '.' . $record['exchange'],
		));

		$record = $factory->createFromCsvRecord($mapper, [
			'raw_symbol' => 'AAPL',
			'exchange' => 'NASDAQ',
		]);

		self::assertSame('AAPL.NASDAQ', $record->ticker);
	}

	public function testNullMappingProducesNullField(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(ticker: 'symbol'));

		$record = $factory->createFromCsvRecord($mapper, ['symbol' => 'AAPL']);

		self::assertNull($record->isin);
		self::assertNull($record->notes);
		self::assertNull($record->price);
	}

	public function testEmptyStringValueIsTreatedAsNull(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(ticker: 'symbol', notes: 'notes'));

		$record = $factory->createFromCsvRecord($mapper, [
			'symbol' => 'AAPL',
			'notes' => '',
		]);

		self::assertNull($record->notes);
	}

	public function testNumericFieldsAreCoercedToDecimal(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(
			units: 'units',
			price: 'price',
			tax: 'tax',
			fee: 'fee',
			total: 'total',
		));

		$record = $factory->createFromCsvRecord($mapper, [
			'units' => '5',
			'price' => '100.25',
			'tax' => '0.50',
			'fee' => '1.00',
			'total' => '501.25',
		]);

		self::assertEquals(new Decimal('5'), $record->units);
		self::assertEquals(new Decimal('100.25'), $record->price);
		self::assertEquals(new Decimal('0.50'), $record->tax);
		self::assertEquals(new Decimal('1.00'), $record->fee);
		self::assertEquals(new Decimal('501.25'), $record->total);
	}

	public function testDateFieldIsCoercedToDateTimeImmutable(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(created: 'date'));

		$record = $factory->createFromCsvRecord($mapper, ['date' => '2024-03-15 10:00:00']);

		self::assertEquals(new DateTimeImmutable('2024-03-15 10:00:00'), $record->created);
	}

	public function testActionTypeIsLowercasedAndMarketMicIsUppercased(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(marketMic: 'mic', actionType: 'action'));

		$record = $factory->createFromCsvRecord($mapper, ['action' => 'Buy', 'mic' => 'xnys']);

		self::assertSame('buy', $record->actionType);
		self::assertSame('XNYS', $record->marketMic);
	}

	public function testCountryIsUppercased(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(country: 'country'));

		$record = $factory->createFromCsvRecord($mapper, ['country' => 'de']);

		self::assertSame('DE', $record->country);
	}

	public function testBooleanFieldIsCoercedFromTruthyString(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(isAdjusted: 'adjusted'));

		$truthyRecord = $factory->createFromCsvRecord($mapper, ['adjusted' => '1']);
		$falseyRecord = $factory->createFromCsvRecord($mapper, ['adjusted' => '0']);

		self::assertTrue($truthyRecord->isAdjusted);
		self::assertFalse($falseyRecord->isAdjusted);
	}

	public function testMissingCsvColumnYieldsNull(): void
	{
		$factory = new TransactionRecordFactory();
		$mapper = $this->makeMapper(new MappingDto(ticker: 'missing_column'));

		$record = $factory->createFromCsvRecord($mapper, ['symbol' => 'AAPL']);

		self::assertNull($record->ticker);
	}

	private function makeMapper(MappingDto $mapping): MapperInterface
	{
		$mapper = self::createStub(MapperInterface::class);
		$mapper->method('getMapping')->willReturn($mapping);
		return $mapper;
	}
}
