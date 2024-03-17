<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper;

use League\Csv\Reader;
use const PATHINFO_EXTENSION;

abstract class CsvMapper implements CsvMapperInterface
{
	/** @return list<array<string, string>> */
	public function getRecords(string $content): array
	{
		$csv = Reader::createFromString($content);
		$csv->setDelimiter($this->getCsvDelimiter());
		$csv->setHeaderOffset(0);

		/** @var list<array<string, string>> $records */
		$records = iterator_to_array($csv->getRecords());
		return $records;
	}

	public function getCsvDelimiter(): string
	{
		return ',';
	}

	public function check(string $content, string $fileName): bool
	{
		$extension = pathinfo($fileName, PATHINFO_EXTENSION);
		return $extension === 'csv';
	}
}
