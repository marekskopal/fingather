<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;

final class LightyearMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Lightyear;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Transaction type',
			created: 'Date',
			ticker: 'Ticker symbol',
			isin: 'ISIN',
			units: 'Shares',
			price: 'Share price',
			total: 'Total',
			currency: 'Currency',
			fee: 'Fee',
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
			array_key_exists('Date', $records[1]) &&
			array_key_exists('Transaction type', $records[1]) &&
			array_key_exists('Ticker symbol', $records[1]) &&
			array_key_exists('ISIN', $records[1]) &&
			array_key_exists('Shares', $records[1]) &&
			array_key_exists('Share price', $records[1]) &&
			array_key_exists('Total', $records[1]) &&
			array_key_exists('Currency', $records[1]);
	}
}
