<?php

declare(strict_types=1);

namespace FinGather\Dto;

use function Safe\json_decode;

readonly class ImportStartDto
{
	/** @param list<ImportMappingDto> $importMappings */
	public function __construct(public int $importId, public array $importMappings)
	{
	}

	/**
	 * @param array{
	 *     importId: int,
	 *     importMappings: list<array{
	 *         importTicker: string,
	 *         tickerId: int
	 *     }>
	 * } $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			importId: $data['importId'],
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
		 *     importId: int,
		 *     importMappings: list<array{
		 *         importTicker: string,
		 *         tickerId: int
		 *     }>
		 * } $data
		 */
		$data = json_decode($json, assoc: true);
		return self::fromArray($data);
	}
}
