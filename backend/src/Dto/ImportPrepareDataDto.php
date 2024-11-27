<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @implements ArrayFactoryInterface<array{
 *     uuid: string,
 *     importDataFile: array{
 *         fileName: string,
 *         contents: string
 *     },
 * }>
 */
final readonly class ImportPrepareDataDto implements ArrayFactoryInterface
{
	public function __construct(public UuidInterface $uuid, public ImportDataFileDto $importDataFile)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(
			uuid: Uuid::fromString($data['uuid']),
			importDataFile: ImportDataFileDto::fromArray($data['importDataFile']),
		);
	}
}
