<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

class RevolutMapper implements MapperInterface
{
	/** @return array<string, string> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'Type',
			'created' => 'Date',
			'ticker' => 'Ticker',
			'units' => 'Quantity',
			'priceUnit' => 'Price per share',
			'currency' => 'Currency',
			'exchangeRate' => 'FX Rate',
			'total' => 'Total Amount',
		];
	}
}
