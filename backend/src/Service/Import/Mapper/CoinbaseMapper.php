<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\Dto\MoneyValueDto;
use function Safe\preg_match;

final class CoinbaseMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Coinbase;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Transaction Type',
			created: 'Timestamp',
			ticker: 'Asset',
			units: 'Quantity Transacted',
			price: fn (array $record): string => $this->getMoneyValue($record['Price at Transaction'])->value ?? '0',
			currency: 'Price Currency',
			fee: fn (array $record): string => $this->getMoneyValue($record['Fees and/or Spread'])->value ?? '0',
			feeCurrency: 'Price Currency',
			importIdentifier: 'ID',
			notes: 'Notes',
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
			array_key_exists('Transaction Type', $records[1]) &&
			array_key_exists('Timestamp', $records[1]) &&
			array_key_exists('Asset', $records[1]) &&
			array_key_exists('Quantity Transacted', $records[1]) &&
			array_key_exists('ID', $records[1]);
	}

	/** @return list<int> */
	public function getAllowedMarketIds(): array
	{
		return [83];
	}

	protected function sanitizeContent(string $content): string
	{
		$lines = explode("\n", $content);

		// Remove first 3 lines
		$lines = array_slice($lines, 3);

		return implode("\n", $lines);
	}

	private function getMoneyValue(string $value): MoneyValueDto
	{
		preg_match('/^([^\d]+)([\d.]+)$/', $value, $matches);
		return new MoneyValueDto(
			//@phpstan-ignore-next-line
			$matches[2],
			//@phpstan-ignore-next-line
			$matches[1],
		);
	}
}
