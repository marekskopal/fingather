<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

class Trading212Mapper implements MapperInterface
{
	/** @return array<string, string> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'Action',
			'created' => 'Time',
			'ticker' => 'Ticker',
			'units' => 'No. of shares',
			'priceUnit' => 'Price / share',
			'currency' => 'Currency (Price / share)',
			'exchangeRate' => 'Exchange rate',
			'feeConversion' => 'Currency conversion fee (CZK)',
			'importIdentifier' => 'ID',
			'total' => 'Total (CZK)',
		];
	}
}
