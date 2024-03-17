<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;

class XtbMapper extends CsvMapper
{
	private const string Action = 'Action';
	private const string Type = 'Type';
	private const string Units = 'Units';
	private const string Price = 'Price';

	public function getImportType(): BrokerImportTypeEnum
	{
		return BrokerImportTypeEnum::Xtb;
	}

	/** @return array<string, string|callable> */
	public function getMapping(): array
	{
		return [
			'actionType' => 'Type',
			'created' => 'Time',
			'ticker' => fn (array $record): string => substr($record['Symbol'], 0, (int) strrpos($record['Symbol'], '.')),
			'units' => fn (array $record): ?string => $this->getInfoFromComment($record['Comment'])[self::Units],
			'price' => fn (array $record): ?string => $this->getInfoFromComment($record['Comment'])[self::Price],
			'importIdentifier' => 'ID',
		];
	}

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
}
