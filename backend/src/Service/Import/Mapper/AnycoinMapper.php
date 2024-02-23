<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Utils\DateTimeUtils;
use Safe\DateTimeImmutable;

class AnycoinMapper extends CsvMapper
{
	/** @return array<string, string|callable> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'ACTION',
			'created' => fn (array $record): string => DateTimeUtils::formatZulu(
				DateTimeImmutable::createFromFormat('j. n. Y H:i:s', $record['DATE']),
			),
			'ticker' => fn (array $record): string => substr($record['SYMBOL'], 0, 3),
			'units' => 'QUANTY',
			'price' => 'PRICE',
			'currency' => fn (array $record): string => substr($record['SYMBOL'], 4, 3),
			'importIdentifier' => 'UID',
		];
	}
}
