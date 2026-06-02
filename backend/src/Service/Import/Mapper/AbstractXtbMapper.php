<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use PhpOffice\PhpSpreadsheet\Shared\Date;

abstract class AbstractXtbMapper extends XlsxMapper
{
	protected const string Id = 'Id';
	protected const string Symbol = 'Symbol';
	protected const string Type = 'Type';
	protected const string Volume = 'Volume';
	protected const string Price = 'Price';
	protected const string Total = 'Total';
	protected const string Created = 'Created';
	protected const string Currency = 'Currency';
	protected const string Tax = 'Tax';

	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Xtb;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: self::Type,
			country: fn (array $record): ?string => $this->countryFromSymbol($record[self::Symbol]),
			created: fn (array $record): string => Date::excelToDateTimeObject((float) $record[self::Created])->format('Y-m-d H:i:s'),
			ticker: fn (array $record): string => substr($record[self::Symbol], 0, (int) strrpos($record[self::Symbol], '.')),
			units: self::Volume,
			price: self::Price,
			total: self::Total,
			currency: self::Currency,
			tax: self::Tax,
			importIdentifier: self::Id,
		);
	}

	protected function countryFromSymbol(string $symbol): ?string
	{
		$dotPos = strrpos($symbol, '.');
		if ($dotPos === false) {
			return null;
		}

		// XTB suffixes are exchange/country tags (".DE", ".UK", etc.). Map them to the
		// markets.country code so the resolver can scope ticker lookups to the right
		// country and avoid colliding with same-named tickers on other exchanges
		// (e.g. MC.FR is LVMH on Paris, not Moelis on NYSE).
		return match (substr($symbol, $dotPos + 1)) {
			'DE' => 'DE',
			'NL' => 'NL',
			'US' => 'US',
			'FR' => 'FR',
			'UK' => 'GB',
			'IT' => 'IT',
			'CH' => 'CH',
			'ES' => 'ES',
			'PL' => 'PL',
			default => null,
		};
	}

	/** @return array{action: string, volume: string}|null */
	protected function parseOperationDetails(string $comment): ?array
	{
		// Volume can be either a single number (e.g. "OPEN BUY 3 @ 7.69") or
		// a partial-trade pair "this_volume/total_position_volume" (e.g. "CLOSE BUY 0.4511/0.5846 @ 1094.50").
		if (preg_match('/^(OPEN|CLOSE)\s+(BUY|SELL)\s+([\d.]+)(?:\s*\/\s*[\d.]+)?\s+@\s+([\d.]+)$/', $comment, $matches) !== 1) {
			return null;
		}

		$action = $matches[1] === 'CLOSE' ? 'SELL' : 'BUY';
		$volume = $matches[3];

		return [
			'action' => $action,
			'volume' => $volume,
		];
	}

	protected function parseDividendPricePerShare(string $comment): ?string
	{
		if (preg_match('/(?:corr\s+)?[\w.]+\s+\w+\s+([\d.]+)\s*\/\s*SHR/', $comment, $matches) !== 1) {
			return null;
		}

		return $matches[1];
	}
}
