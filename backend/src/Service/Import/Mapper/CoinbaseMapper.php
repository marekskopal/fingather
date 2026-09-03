<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Service\Import\Mapper\Dto\MoneyValueDto;
use Override;

final class CoinbaseMapper extends CsvMapper
{
	// Coinbase reports staked ETH under its own "ETH2" symbol, which no market knows.
	private const array TickerAliases = [
		'ETH2' => 'ETH',
	];

	// Fiat cash movements have no asset to import and would only be logged as "Ticker not found".
	private const array SkippedTransactionTypes = [
		'deposit',
		'exchange deposit',
		'withdrawal',
		'exchange withdrawal',
	];

	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Coinbase;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Transaction Type',
			created: 'Timestamp',
			ticker: fn (array $record): string => self::TickerAliases[$record['Asset']] ?? $record['Asset'],
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
			isset($records[0]) &&
			array_key_exists('Transaction Type', $records[0]) &&
			array_key_exists('Timestamp', $records[0]) &&
			array_key_exists('Asset', $records[0]) &&
			array_key_exists('Quantity Transacted', $records[0]) &&
			array_key_exists('ID', $records[0]);
	}

	/** @return list<array<string, string>> */
	#[Override]
	public function getRecords(string $content): array
	{
		return array_values(array_filter(
			parent::getRecords($content),
			static fn (array $record): bool => !in_array(
				strtolower($record['Transaction Type'] ?? ''),
				self::SkippedTransactionTypes,
				true,
			),
		));
	}

	/** @return list<int> */
	#[Override]
	public function getAllowedMarketIds(): array
	{
		return [83];
	}

	#[Override]
	protected function sanitizeContent(string $content): string
	{
		$lines = explode("\n", $content);

		// Remove first 3 lines
		$lines = array_slice($lines, 3);

		return implode("\n", $lines);
	}

	private function getMoneyValue(string $value): MoneyValueDto
	{
		$matches = [];
		preg_match('/^([^\d]+)([\d.]+)$/', $value, $matches);
		return new MoneyValueDto($matches[2] ?? '0', $matches[1] ?? '');
	}
}
