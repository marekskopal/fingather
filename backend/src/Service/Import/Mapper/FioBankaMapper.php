<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;

final class FioBankaMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::FioBanka;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Směr',
			created: 'Datum obchodu',
			ticker: fn (array $record): string => str_replace('BAA', '', $record['Symbol']),
			units: fn (array $record): string => str_replace(',', '.', $record['Počet']),
			price: fn (array $record): string => str_replace(',', '.', $record['Cena']),
			currency: 'Měna',
			fee: fn (array $record): string => str_replace(',', '.', $record['Poplatky v CZK']),
			feeCurrency: fn (): string => 'CZK',
			importIdentifier: fn (array $record): string =>
					$record['Datum obchodu'] . '|' . $record['Symbol'] . '|' . $record['Směr'],
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
			array_key_exists('Datum obchodu', $records[1]) &&
			array_key_exists('Směr', $records[1]) &&
			array_key_exists('Symbol', $records[1]) &&
			array_key_exists('Cena', $records[1]) &&
			array_key_exists('Počet', $records[1]);
	}

	/** @return list<int> */
	#[Override]
	public function getAllowedMarketIds(): array
	{
		return [
			46,
		];
	}

	#[Override]
	public function getCsvDelimiter(): string
	{
		return ';';
	}

	#[Override]
	protected function sanitizeContent(string $content): string
	{
		$content = @iconv('WINDOWS-1250', 'UTF-8//TRANSLIT', $content);
		if ($content === false) {
			return '';
		}

		$lines = explode("\n", $content);

		// Remove first 7 lines
		$lines = array_slice($lines, 7);

		return implode("\n", $lines);
	}
}
