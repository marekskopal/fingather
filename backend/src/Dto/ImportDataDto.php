<?php

declare(strict_types=1);

namespace FinGather\Dto;

use function Safe\json_decode;

readonly class ImportDataDto
{
	public function __construct(public int $brokerId, public string $data)
	{
	}

	public static function fromArray(array $data): self
	{
		return new self($data['brokerId'], $data['string']);
	}

	public static function fromJson(string $json)
	{
		/** @var array{brokerId: int, data: string} $data */
		$data = json_decode($json, assoc: true);
		return self::fromArray($data);
	}
}
