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
		$mappedRecord = $this->mapCsvRecord($mapper, $csvRecord);

		return new TransactionRecord(
			ticker: $mappedRecord['ticker'] ?? null,
			isin: $mappedRecord['isin'] ?? null,
			marketMic: isset($mappedRecord['marketMic']) ? strtoupper($mappedRecord['marketMic']) : null,
			actionType: strtolower($mappedRecord['actionType'] ?? ''),
			created: new DateTimeImmutable($mappedRecord['created'] ?? ''),
			units: isset($mappedRecord['units']) ? new Decimal($mappedRecord['units']) : null,
			price: isset($mappedRecord['price']) ? new Decimal($mappedRecord['price']) : null,
			currency: $mappedRecord['currency'],
			tax: isset($mappedRecord['tax']) ? new Decimal($mappedRecord['tax']) : null,
			taxCurrency: $mappedRecord['taxCurrency'] ?? null,
			fee: isset($mappedRecord['fee']) ? new Decimal($mappedRecord['fee']) : null,
			feeCurrency: $mappedRecord['feeCurrency'] ?? null,
			notes: $mappedRecord['notes'] ?? null,
			importIdentifier: $mappedRecord['importIdentifier'] ?? null,
		);
	}

	/**
	 * @param array<string, string> $csvRecord
	 * @return array<string, string|null>
	 */
	private function mapCsvRecord(MapperInterface $mapper, array $csvRecord): array
	{
		$mappedRecord = [];

		foreach ($mapper->getMapping() as $attribute => $recordKey) {
			if ($recordKey === null) {
				$mappedRecord[$attribute] = null;
				continue;
			}

			if (!is_string($recordKey)) {
				$mappedRecord[$attribute] = $recordKey($csvRecord);
				continue;
			}

			$mappedRecord[$attribute] = $csvRecord[$recordKey] ?? null;
		}

		return array_map(fn(?string $item): ?string => $item !== '' ? $item : null, $mappedRecord);
	}
}
