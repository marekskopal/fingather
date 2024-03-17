<?php

declare(strict_types=1);

namespace FinGather\Dto;

use function Safe\json_decode;

readonly class ImportDataDto
{
	/** @param list<ImportDataFileDto> $importDataFiles */
	public function __construct(public array $importDataFiles)
	{
	}

	/** @param list<array{fileName: string, contents: string}> $data */
	public static function fromArray(array $data): self
	{
		return new self(
			importDataFiles: array_map(
				fn (array $importDataFile): ImportDataFileDto => ImportDataFileDto::fromArray($importDataFile),
				$data,
			),
		);
	}

	public static function fromJson(string $json): self
	{
		/** @var list<array{fileName: string, contents: string}> $data */
		$data = json_decode($json, assoc: true);
		return self::fromArray($data);
	}
}
