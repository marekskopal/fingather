<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;

final class Trading212Mapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Trading212;
	}

	/** @return array<string, string|callable> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'Action',
			'created' => 'Time',
			'ticker' => 'Ticker',
			'isin' => 'ISIN',
			'units' => 'No. of shares',
			'price' => fn (array $record): string => str_starts_with(
				$record['Action'],
				'Dividend',
			) ? $record['Total'] : $record['Price / share'],
			'currency' => fn (array $record): string => str_starts_with(
				$record['Action'],
				'Dividend',
			) ? $record['Currency (Total)'] : $record['Currency (Price / share)'],
			'tax' => 'Stamp duty reserve tax',
			'taxCurrency' => 'Currency (Stamp duty reserve tax)',
			'fee' => 'Currency conversion fee',
			'feeCurrency' => 'Currency (Currency conversion fee)',
			'importIdentifier' => fn (array $record): string =>
				$record['ID'] !== '' ?
					$record['ID'] :
					$record['Time'] . '|' . $record['Ticker'] . '|' . $record['No. of shares'],
		];
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
			array_key_exists('Action', $records[1]) &&
			array_key_exists('Time', $records[1]) &&
			array_key_exists('Ticker', $records[1]) &&
			array_key_exists('No. of shares', $records[1]);
	}

	/** @return list<int>|null */
	public function getAllowedMarketIds(): ?array
	{
		return [
			//NASDAQ
			1,
			2,
			3,
			4,
			//NYSE
			5,
			6,
			7,
			//BorsaItaliana
			33,
			//EuronextParis
			16,
			//OTCMarkets
			8,
			9,
			10,
			11,
			12,
			//EuronextAmsterdam
			18,
			//SIXSwissExchange
			39,
			//WienerBörse
			28,
			//TorontoStockExchange
			52,
			//BolsadeMadrid
			35,
			//LondonStockExchange
			15,
			//EuronextLisbon
			19,
			//DeutscheBörseXetra
			24,
			//EuronextBrussels
			17,
		];
	}
}
