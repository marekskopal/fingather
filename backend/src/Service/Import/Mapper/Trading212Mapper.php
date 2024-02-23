<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

class Trading212Mapper implements MapperInterface
{
	/** @return array<string, string|callable> */
	public function getCsvMapping(): array
	{
		return [
			'actionType' => 'Action',
			'created' => 'Time',
			'ticker' => 'Ticker',
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

	public function getCsvDelimiter(): string
	{
		return ',';
	}
}
