<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Service\Import\Mapper\Dto\MappingDto;
use Override;

final class XtbMapper extends CsvMapper
{
	private const string Action = 'Action';
	private const string Type = 'Type';
	private const string Units = 'Units';
	private const string Price = 'Price';

	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Xtb;
	}

	public function getMapping(): MappingDto
	{
		$mappingDto = new MappingDto(
			actionType: 'Type',
			created: 'Time',
			ticker: fn (array $record): string => substr($record['Symbol'], 0, (int) strrpos($record['Symbol'], '.')),
			units: fn (array $record): ?string => $this->getInfoFromComment($record['Comment'])[self::Units],
			price: fn (array $record): ?string => $this->getInfoFromComment($record['Comment'])[self::Price],
			importIdentifier: 'ID',
		);
		return $mappingDto;
	}

	#[Override]
	public function getCsvDelimiter(): string
	{
		return ';';
	}

	/** @return array{Action: string|null, Type: string|null, Units: string|null, Price: string|null} */
	private function getInfoFromComment(string $comment): array
	{
		[
			$action,
			$type,
			$units,
			//@phpcs:ignore
			$divider,
			$price,
		] = explode(' ', $comment);

		return [
			self::Action => $action,
			self::Type => $type,
			self::Units => $units,
			self::Price => $price,
		];
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
			array_key_exists('Type', $records[1]) &&
			array_key_exists('Time', $records[1]) &&
			array_key_exists('Symbol', $records[1]) &&
			array_key_exists('Comment', $records[1]) &&
			array_key_exists('ID', $records[1]);
	}
}
