<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;

final class FreetradeMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Freetrade;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: fn (array $record): string => $record['Type'] === 'ORDER'
				? $record['Order Type']
				: $record['Type'],
			created: 'Timestamp',
			ticker: 'Title',
			units: 'Total Shares Bought / Sold',
			price: 'Price per Share in Account Currency',
			total: 'GBP Amount (ex. fees)',
			currency: 'Account Currency',
			fee: 'Stamp Duty',
		);
	}

	#[Override]
	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$records = $this->getRecords($content);

		return
			isset($records[1]) &&
			array_key_exists('Title', $records[1]) &&
			array_key_exists('Type', $records[1]) &&
			array_key_exists('Timestamp', $records[1]) &&
			array_key_exists('Account Currency', $records[1]) &&
			array_key_exists('Order Type', $records[1]) &&
			array_key_exists('Total Shares Bought / Sold', $records[1]) &&
			array_key_exists('Price per Share in Account Currency', $records[1]);
	}
}
