<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;

final class DegiroMapper extends CsvMapper
{
	private const DescriptionRegex = '/(?<action>[^ ]+) (?<units>[0-9]+) (?<name>[^@]+)@(?<price>[0-9]+(,[0-9]+)?) (?<currency>[^ ]+)/';

	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Degiro;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Popis',
			created: fn (array $record): string => $record['Datum'] . ' ' . $record['Čas'],
			isin: 'ISIN',
			units: fn (array $record): ?string => $this->parseFromDescription($record['Popis'], 'units'),
			price: fn (array $record): ?string => $this->parseFromDescription($record['Popis'], 'price'),
			total: 'Pohyb2',
			currency: fn (array $record): ?string => $record['Pohyb'] ?? $this->parseFromDescription($record['Popis'], 'currency'),
			tax: fn(array $record): ?string => str_contains(strtolower($record['Popis']), 'tax') || str_contains(
				strtolower($record['Popis']),
				'daň',
			) ? $record['Pohyb2'] : null,
			taxCurrency: fn(array $record): ?string => str_contains(strtolower($record['Popis']), 'tax') || str_contains(
				strtolower($record['Popis']),
				'daň',
			) ? $record['Pohyb'] : null,
			fee: fn(array $record): ?string => str_contains(strtolower($record['Popis']), 'fee') || str_contains(
				strtolower($record['Popis']),
				'poplatek',
			) ? $record['Pohyb2'] : null,
			feeCurrency: fn(array $record): ?string => str_contains(strtolower($record['Popis']), 'fee') || str_contains(
				strtolower($record['Popis']),
				'poplatek',
			) ? $record['Pohyb'] : null,
			importIdentifier: fn (array $record): string => implode(' ', [
				$record['Datum'],
				$record['Čas'],
				$record['Popis'],
				$record['ISIN'],
				$record['ID objednávky'],
			]),
		);
	}

	public function check(string $content, string $fileName): bool
	{
		if (!parent::check($content, $fileName)) {
			return false;
		}

		$records = $this->getRecords($content);

		return
			isset($records[1]) &&
			array_key_exists('Datum', $records[1]) &&
			array_key_exists('Čas', $records[1]) &&
			array_key_exists('Produkt', $records[1]) &&
			array_key_exists('ISIN', $records[1]) &&
			array_key_exists('Popis', $records[1]);
	}

	#[Override]
	protected function sanitizeContent(string $content): string
	{
		$lines = explode("\n", $content);

		$pos = strrpos($lines[0], 'Datum');
		if ($pos !== false) {
			$lines[0] = substr_replace($lines[0], 'Datum2', $pos, strlen('Datum'));
		}

		$columns = explode(',', $lines[0]);

		foreach ($columns as $key => $column) {
			if ($key === 0 || $column !== '') {
				continue;
			}

			$columns[$key] = $columns[$key - 1] . '2';
		}
		$lines[0] = implode(',', $columns);

		return implode("\n", $lines);
	}

	private function parseFromDescription(string $description, string $variableName): ?string
	{
		$matches = [];
		if (preg_match(self::DescriptionRegex, $description, $matches) === 0) {
			return null;
		}
		//@phpstan-ignore-next-line
		return str_replace(',', '.', $matches[$variableName]);
	}
}
