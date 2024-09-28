<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use function Safe\preg_match;

final class DegiroMapper extends CsvMapper
{
	private const DecsriptionRegex = '/(?<action>[^ ]+) (?<units>[0-9]+) (?<name>[^ ]+) - [^@]+@(?<price>[0-9]+,[0-9]+) (?<currency>[^ ]+)/';

	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Degiro;
	}

	/** @return array<string, string|callable> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'Popis',
			'created' => fn (array $record): string => $record['Datum'] . ' ' . $record['Čas'],
			'isin' => 'ISIN',
			'units' => function (array $record): ?string {
				if (preg_match(self::DecsriptionRegex, $record['Popis'], $matches) === 0) {
					return null;
				}
				//@phpstan-ignore-next-line
				return str_replace(',', '.', $matches['units']);
			},
			'price' => function (array $record): ?string {
				if (preg_match(self::DecsriptionRegex, $record['Popis'], $matches) === 0) {
					return null;
				}
				//@phpstan-ignore-next-line
				return str_replace(',', '.', $matches['price']);
			},
			'currency' => function (array $record): ?string {
				if (preg_match(self::DecsriptionRegex, $record['Popis'], $matches) === 0) {
					return null;
				}
				//@phpstan-ignore-next-line
				return str_replace(',', '.', $matches['currency']);
			},
			'tax' => fn(array $record): ?string => str_contains(strtolower($record['Popis']), 'tax') || str_contains(
				strtolower($record['Popis']),
				'daň',
			) ? $record['Pohyb2'] : null,
			'taxCurrency' => fn(array $record): ?string => str_contains(strtolower($record['Popis']), 'tax') || str_contains(
				strtolower($record['Popis']),
				'daň',
			) ? $record['Pohyb'] : null,
			'fee' => fn(array $record): ?string => str_contains(strtolower($record['Popis']), 'fee') || str_contains(
				strtolower($record['Popis']),
				'poplatek',
			) ? $record['Pohyb2'] : null,
			'feeCurrency' => fn(array $record): ?string => str_contains(strtolower($record['Popis']), 'fee') || str_contains(
				strtolower($record['Popis']),
				'poplatek',
			) ? $record['Pohyb'] : null,
			'importIdentifier' => 'ID objednávky',
		];
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

	protected function sanitizeContent(string $content): string
	{
		$lines = explode("\n", $content);

		$pos = strrpos($lines[0], 'Datum');
		if ($pos !== false) {
			$lines[0] = substr_replace($lines[0], 'Datum2', $pos, strlen('Datum'));
		}

		$columns = explode(',', $lines[0]);

		foreach ($columns as $key => $column) {
			if ($column !== '') {
				continue;
			}

			$columns[$key] = $columns[$key - 1] . '2';
		}
		$lines[0] = implode(',', $columns);

		return implode("\n", $lines);
	}
}
