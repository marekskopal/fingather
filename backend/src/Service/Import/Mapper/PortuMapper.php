<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;

final class PortuMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Portu;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'Typ',
			created: 'Datum',
			ticker: fn (array $record): string =>
				explode(' ', $record['Symbol'])[0],
			units: fn (array $record): string => str_replace(',', '.', $record['Kusy / Pozice']),
			price: 'Cena',
			currency: 'Měna',
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
			array_key_exists('Typ', $records[1]) &&
			array_key_exists('Datum', $records[1]) &&
			array_key_exists('Symbol', $records[1]) &&
			array_key_exists('Kusy / Pozice', $records[1]) &&
			array_key_exists('Cena', $records[1]) &&
			array_key_exists('Měna', $records[1]);
	}
}
