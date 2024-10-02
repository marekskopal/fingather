<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function Safe\json_decode;

final readonly class ImportPrepareDataDto
{
	public function __construct(public UuidInterface $uuid, public ImportDataFileDto $importDataFile)
	{
	}

	/** @param array{uuid: string, importDataFile: array{fileName: string, contents: string}} $data */
	private static function fromArray(array $data): self
	{
		return new self(
			uuid: Uuid::fromString($data['uuid']),
			importDataFile: ImportDataFileDto::fromArray($data['importDataFile']),
		);
	}

	public static function fromJson(string $json): self
	{
		/** @var array{uuid: string, importDataFile: array{fileName: string, contents: string}} $data */
		$data = json_decode($json, assoc: true);
		return self::fromArray($data);
	}
}
