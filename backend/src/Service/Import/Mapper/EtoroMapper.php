<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;

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
			created: function (array $record): ?string {
				$dateTime = DateTimeImmutable::createFromFormat('d/m/Y H:i:s', $record['A']);
				return $dateTime instanceof DateTimeImmutable ? $dateTime->format(
					'Y-m-d H:i:s',
				) : null;
			},
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
