<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

class InteractiveBrokersMapper implements MapperInterface
{
	/** @return array<string, string|callable> */
	public function getCsvMapping(): array
	{
		return [
			'actionType' => 'Buy/Sell',
			'created' => fn (array $record): string => str_replace(';', ' ', $record['DateTime']),
			'ticker' => 'Symbol',
			'marketMic' => fn (array $record): string => 'X' . $record['Exchange'],
			'units' => 'Quantity',
			'price' => 'TradePrice',
			'currency' => 'CurrencyPrimary',
			'tax' => 'Taxes',
			'fee' => fn (array $record): string => (string) abs((int) $record['IBCommission']),
			'feeCurrency' => 'IBCommissionCurrency',
			'importIdentifier' => 'TransactionID',
		];
	}

	public function getCsvDelimiter(): string
	{
		return ',';
	}
}
