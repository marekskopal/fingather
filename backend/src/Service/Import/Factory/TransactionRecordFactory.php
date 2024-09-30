<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Factory;

use Decimal\Decimal;
use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Mapper\MapperInterface;
use Safe\DateTimeImmutable;

final class TransactionRecordFactory
{
	/** @param array<string, string> $csvRecord */
	public function createFromCsvRecord(MapperInterface $mapper, array $csvRecord): TransactionRecord
	{
		$mapping = $mapper->getMapping();

		return new TransactionRecord(
			ticker: $this->mapCsvRecordColumn($mapping->ticker, $csvRecord),
			isin: $this->mapCsvRecordColumn($mapping->isin, $csvRecord),
			marketMic: $this->mapCsvRecordColumnToUpper($mapping->actionType, $csvRecord),
			actionType: $this->mapCsvRecordColumnToLower($mapping->actionType, $csvRecord),
			created: $this->mapCsvRecordColumnToDate($mapping->created, $csvRecord),
			units: $this->mapCsvRecordColumnToDecimal($mapping->units, $csvRecord),
			price: $this->mapCsvRecordColumnToDecimal($mapping->price, $csvRecord),
			currency: $this->mapCsvRecordColumn($mapping->currency, $csvRecord),
			tax: $this->mapCsvRecordColumnToDecimal($mapping->tax, $csvRecord),
			taxCurrency: $this->mapCsvRecordColumn($mapping->taxCurrency, $csvRecord),
			fee: $this->mapCsvRecordColumnToDecimal($mapping->fee, $csvRecord),
			feeCurrency: $this->mapCsvRecordColumn($mapping->feeCurrency, $csvRecord),
			notes: $this->mapCsvRecordColumn($mapping->notes, $csvRecord),
			importIdentifier: $this->mapCsvRecordColumn($mapping->importIdentifier, $csvRecord),
		);
	}

	/** @param array<string, string> $csvRecord */
	private function mapCsvRecordColumn(string|callable|null $mapping, array $csvRecord): ?string
	{
		if ($mapping === null) {
			return null;
		}

		if (is_callable($mapping)) {
			return $this->sanitizeEmptyItem($mapping($csvRecord));
		}

		return $this->sanitizeEmptyItem($csvRecord[$mapping] ?? null);
	}

	/** @param array<string, string> $csvRecord */
	private function mapCsvRecordColumnToDecimal(string|callable|null $mapping, array $csvRecord): ?Decimal
	{
		$mappedCsvRecordColumn = $this->mapCsvRecordColumn($mapping, $csvRecord);
		return isset($mappedCsvRecordColumn) ? new Decimal($mappedCsvRecordColumn) : null;
	}

	/** @param array<string, string> $csvRecord */
	private function mapCsvRecordColumnToDate(string|callable|null $mapping, array $csvRecord): ?DateTimeImmutable
	{
		$mappedCsvRecordColumn = $this->mapCsvRecordColumn($mapping, $csvRecord);
		return isset($mappedCsvRecordColumn) ? new DateTimeImmutable($mappedCsvRecordColumn) : null;
	}

	/** @param array<string, string> $csvRecord */
	private function mapCsvRecordColumnToUpper(string|callable|null $mapping, array $csvRecord): ?string
	{
		$mappedCsvRecordColumn = $this->mapCsvRecordColumn($mapping, $csvRecord);
		return isset($mappedCsvRecordColumn) ? strtoupper($mappedCsvRecordColumn) : null;
	}

	/** @param array<string, string> $csvRecord */
	private function mapCsvRecordColumnToLower(string|callable|null $mapping, array $csvRecord): ?string
	{
		$mappedCsvRecordColumn = $this->mapCsvRecordColumn($mapping, $csvRecord);
		return isset($mappedCsvRecordColumn) ? strtolower($mappedCsvRecordColumn) : null;
	}

	private function sanitizeEmptyItem(?string $item): ?string
	{
		return $item !== '' ? $item : null;
	}
}
