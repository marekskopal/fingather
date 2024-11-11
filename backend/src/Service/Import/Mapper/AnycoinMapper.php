<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use FinGather\Utils\DateTimeUtils;

final class AnycoinMapper extends CsvMapper
{
	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Anycoin;
	}

	public function getMapping(): MappingDto
	{
		return new MappingDto(
			actionType: 'ACTION',
			created: function (array $record): ?string {
				$datetime = DateTimeImmutable::createFromFormat('j. n. Y H:i:s', $record['DATE']);
				return $datetime instanceof DateTimeImmutable ? DateTimeUtils::formatZulu($datetime) : null;
			},
			ticker: fn (array $record): string => substr($record['SYMBOL'], 0, 3),
			units: 'QUANTY',
			price: 'PRICE',
			currency: fn (array $record): string => substr($record['SYMBOL'], 4, 3),
			importIdentifier: 'UID',
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
			array_key_exists('ACTION', $records[1]) &&
			array_key_exists('DATE', $records[1]) &&
			array_key_exists('SYMBOL', $records[1]) &&
			array_key_exists('QUANTY', $records[1]) &&
			array_key_exists('PRICE', $records[1]) &&
			array_key_exists('UID', $records[1]);
	}
}
