<?php

declare(strict_types=1);

namespace FinGather\Dto;

use function Safe\json_decode;

final readonly class ImportPrepareDataDto
{
	public function __construct(public ?int $importId, public ImportDataFileDto $importDataFile)
	{
	}

	/** @param array{importId: int|null, importDataFile: array{fileName: string, contents: string}} $data */
	private static function fromArray(array $data): self
	{
		return new self(
			importId: $data['importId'] ?? null,
			importDataFile: ImportDataFileDto::fromArray($data['importDataFile']),
		);
	}

	public static function fromJson(string $json): self
	{
		/** @var array{importId: int|null, importDataFile: array{fileName: string, contents: string}} $data */
		$data = json_decode($json, assoc: true);
		return self::fromArray($data);
	}
}
