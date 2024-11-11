<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class ImportStartDto
{
	/** @param list<ImportMappingDto> $importMappings */
	public function __construct(public UuidInterface $uuid, public array $importMappings)
	{
	}

	/**
	 * @param array{
	 *     uuid: string,
	 *     importMappings: list<array{
	 *         importTicker: string,
	 *         tickerId: int,
	 *         brokerId: int,
	 *     }>
	 * } $data
	 */
	private static function fromArray(array $data): self
	{
		return new self(
			uuid: Uuid::fromString($data['uuid']),
			importMappings: array_map(
				fn (array $importMapping): ImportMappingDto => ImportMappingDto::fromArray($importMapping),
				$data['importMappings'],
			),
		);
	}

	public static function fromJson(string $json): self
	{
		/**
		 * @var array{
		 *     uuid: string,
		 *     importMappings: list<array{
		 *         importTicker: string,
		 *         tickerId: int,
		 *         brokerId: int,
		 *     }>
		 * } $data
		 */
		$data = json_decode($json, associative: true);
		return self::fromArray($data);
	}
}
