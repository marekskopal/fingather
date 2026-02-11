<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;

final class EtoroApiMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::EtoroApi;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Action',
			created: 'Date',
			ticker: 'Ticker',
			units: 'Units',
			price: 'Price',
			currency: 'Currency',
			fee: 'Fee',
			feeCurrency: 'FeeCurrency',
			isAdjusted: 'IsAdjusted',
			importIdentifier: 'ImportIdentifier',
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
			array_key_exists('Action', $records[1]) &&
			array_key_exists('Date', $records[1]) &&
			array_key_exists('Ticker', $records[1]) &&
			array_key_exists('Units', $records[1]) &&
			array_key_exists('Price', $records[1]) &&
			array_key_exists('Currency', $records[1]) &&
			array_key_exists('FeeCurrency', $records[1]) &&
			array_key_exists('IsAdjusted', $records[1]) &&
			array_key_exists('ImportIdentifier', $records[1]);
	}
}
