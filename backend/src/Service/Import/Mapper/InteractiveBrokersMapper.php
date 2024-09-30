<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;

final class InteractiveBrokersMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::InteractiveBrokers;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Buy/Sell',
			created: fn (array $record): string => str_replace(';', ' ', $record['DateTime']),
			ticker: 'Symbol',
			isin: 'ISIN',
			marketMic: fn (array $record): string => 'X' . $record['Exchange'],
			units: 'Quantity',
			price: 'TradePrice',
			currency: 'CurrencyPrimary',
			tax: 'Taxes',
			fee: fn (array $record): string => (string) abs((int) $record['IBCommission']),
			feeCurrency: 'IBCommissionCurrency',
			importIdentifier: 'TransactionID',
		);
	}

	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$records = $this->getRecords($content);
		return
			// Check if there is at least one record (header is not counted)
			isset($records[1]) &&
			array_key_exists('Buy/Sell', $records[1]) &&
			array_key_exists('DateTime', $records[1]) &&
			array_key_exists('Symbol', $records[1]) &&
			array_key_exists('Exchange', $records[1]) &&
			array_key_exists('Quantity', $records[1]) &&
			array_key_exists('TradePrice', $records[1]);
	}
}
