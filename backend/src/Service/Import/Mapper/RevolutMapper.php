<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;

class RevolutMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Revolut;
	}

	/** @return array<string, string> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'Type',
			'created' => 'Date',
			'ticker' => 'Ticker',
			'units' => 'Quantity',
			'price' => 'Price per share',
			'currency' => 'Currency',
			'total' => 'Total Amount',
		];
	}
}
