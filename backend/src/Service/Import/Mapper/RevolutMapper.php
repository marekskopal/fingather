<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

class RevolutMapper implements MapperInterface
{
	/** @return array<string, string> */
	public function getCsvMapping(): array
	{
		return [
			'actionType' => 'Type',
			'created' => 'Date',
			'ticker' => 'Ticker',
			'units' => 'Quantity',
			'priceUnit' => 'Price per share',
			'currency' => 'Currency',
			'total' => 'Total Amount',
		];
	}

	public function getTickerMapping(): array
	{
		return [];
	}
}
