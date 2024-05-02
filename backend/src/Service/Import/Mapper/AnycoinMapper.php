<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Utils\DateTimeUtils;
use Safe\DateTimeImmutable;

class AnycoinMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Anycoin;
	}

	/** @return array<string, string|callable> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'ACTION',
			'created' => fn (array $record): string => DateTimeUtils::formatZulu(
				DateTimeImmutable::createFromFormat('j. n. Y H:i:s', $record['DATE']),
			),
			'ticker' => fn (array $record): string => substr($record['SYMBOL'], 0, 3),
			'units' => 'QUANTY',
			'price' => 'PRICE',
			'currency' => fn (array $record): string => substr($record['SYMBOL'], 4, 3),
			'importIdentifier' => 'UID',
		];
	}

	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$records = $this->getRecords($content);
		return
			// Check if there is at least one record (header is not counted)
			isset($records[1]) &&
			array_key_exists('ACTION', $records[1]) &&
			array_key_exists('DATE', $records[1]) &&
			array_key_exists('SYMBOL', $records[1]) &&
			array_key_exists('QUANTY', $records[1]) &&
			array_key_exists('PRICE', $records[1]) &&
			array_key_exists('UID', $records[1]);
	}
}
