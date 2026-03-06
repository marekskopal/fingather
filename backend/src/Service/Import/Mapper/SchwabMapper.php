<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;

final class SchwabMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Schwab;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Action',
			created: 'Date',
			ticker: 'Symbol',
			units: 'Quantity',
			price: fn (array $record): ?string => $this->cleanAmount($record['Price']),
			total: fn (array $record): ?string => $this->cleanAmount($record['Amount']),
			currency: fn (): string => 'USD',
			fee: fn (array $record): ?string => $this->cleanAmount($record['Fees & Comm']),
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
			array_key_exists('Action', $records[1]) &&
			array_key_exists('Symbol', $records[1]) &&
			array_key_exists('Description', $records[1]) &&
			array_key_exists('Quantity', $records[1]) &&
			array_key_exists('Price', $records[1]) &&
			array_key_exists('Fees & Comm', $records[1]) &&
			array_key_exists('Amount', $records[1]);
	}

	#[Override]
	protected function sanitizeContent(string $content): string
	{
		$lines = explode("\n", $content);

		// Remove first line (account description header)
		array_shift($lines);

		// Remove "Transactions Total" summary lines
		$result = [];
		foreach ($lines as $line) {
			if (str_starts_with(ltrim($line, '"'), 'Transactions Total')) {
				continue;
			}
			$result[] = $line;
		}

		return implode("\n", $result);
	}

	private function cleanAmount(string $amount): ?string
	{
		$cleaned = preg_replace('/[^0-9.-]/', '', $amount);
		return $cleaned !== '' && $cleaned !== '-' ? $cleaned : null;
	}
}
