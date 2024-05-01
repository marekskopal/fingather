<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;

class Trading212Mapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Trading212;
	}

	/** @return array<string, string|callable> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'Action',
			'created' => 'Time',
			'ticker' => 'Ticker',
			'isin' => 'ISIN',
			'units' => 'No. of shares',
			'price' => fn (array $record): string => str_starts_with(
				$record['Action'],
				'Dividend',
			) ? $record['Total'] : $record['Price / share'],
			'currency' => fn (array $record): string => str_starts_with(
				$record['Action'],
				'Dividend',
			) ? $record['Currency (Total)'] : $record['Currency (Price / share)'],
			'tax' => 'Stamp duty reserve tax',
			'taxCurrency' => 'Currency (Stamp duty reserve tax)',
			'fee' => 'Currency conversion fee',
			'feeCurrency' => 'Currency (Currency conversion fee)',
			'importIdentifier' => 'ID',
		];
	}

	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$records = $this->getRecords($content);
		return
			array_key_exists('Action', $records[1]) &&
			array_key_exists('Time', $records[1]) &&
			array_key_exists('Ticker', $records[1]) &&
			array_key_exists('No. of shares', $records[1]);
	}
}
