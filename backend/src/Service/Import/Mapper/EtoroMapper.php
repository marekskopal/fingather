<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use Safe\DateTimeImmutable;

class EtoroMapper extends XlsxMapper
{
	/** @return array<string, string|callable|null> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'B',
			'created' => fn (array $record): string => DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $record['A'])->format(
				'Y-m-d H:i:s',
			),
			'ticker' => fn (array $record): ?string => explode('/', $record['C'])[0] ?? null,
			'units' => fn (array $record): ?string => $record['E'] !== '-' ? $record['E'] : null,
			'price' => 'D',
			'currency' => fn (array $record): ?string => explode('/', $record['C'])[1] ?? null,
			'importIdentifier' => 'I',
		];
	}

	protected function getSheetIndex(): int
	{
		return 2;
	}
}
