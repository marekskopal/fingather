<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @implements ArrayFactoryInterface<array{
 *     uuid: string,
 *     importMappings: list<array{
 *         importTicker: string,
 *         tickerId: int,
 *         brokerId: int,
 *     }>
 * }>
 */
final readonly class ImportStartDto implements ArrayFactoryInterface
{
	/** @param list<ImportMappingDto> $importMappings */
	public function __construct(public UuidInterface $uuid, public array $importMappings)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(
			uuid: Uuid::fromString($data['uuid']),
			importMappings: array_map(
				fn (array $importMapping): ImportMappingDto => ImportMappingDto::fromArray($importMapping),
				$data['importMappings'],
			),
		);
	}
}
