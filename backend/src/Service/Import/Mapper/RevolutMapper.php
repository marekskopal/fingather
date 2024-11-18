<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;

final class RevolutMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Revolut;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Type',
			created: 'Date',
			ticker: 'Ticker',
			units: 'Quantity',
			price: fn (array $record): ?string => preg_replace('/[^0-9-.]/', '', $record['Price per share']),
			currency: 'Currency',
			total: fn (array $record): ?string => preg_replace('/[^0-9-.]/', '', $record['Total Amount']),
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
			// Check if there is at least one record (header is not counted)
			isset($records[1]) &&
			array_key_exists('Date', $records[1]) &&
			array_key_exists('Ticker', $records[1]) &&
			array_key_exists('Type', $records[1]) &&
			array_key_exists('Quantity', $records[1]) &&
			array_key_exists('Price per share', $records[1]) &&
			array_key_exists('Total Amount', $records[1]) &&
			array_key_exists('Currency', $records[1]);
	}
}
