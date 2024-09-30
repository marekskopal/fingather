<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Safe\DateTimeImmutable;

final class EtoroMapper extends XlsxMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Etoro;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'B',
			created: fn (array $record): string => DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $record['A'])->format(
				'Y-m-d H:i:s',
			),
			ticker: fn (array $record): string => explode('/', $record['C'])[0],
			units: fn (array $record): ?string => $record['E'] !== '-' ? $record['E'] : null,
			price: 'D',
			currency: fn (array $record): ?string => explode('/', $record['C'])[1] ?? null,
			importIdentifier: 'I',
		);
	}

	public function getSheetIndex(): int
	{
		return 2;
	}
}
