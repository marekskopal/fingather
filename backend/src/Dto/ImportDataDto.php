<?php

declare(strict_types=1);

namespace FinGather\Dto;

use function Safe\base64_decode;
use function Safe\json_decode;

readonly class ImportDataDto
{
	public function __construct(public int $brokerId, public string $data)
	{
	}

	/** @param array{brokerId: int, data: string} $data */
	public static function fromArray(array $data): self
	{
		return new self(
			brokerId: $data['brokerId'],
			data: base64_decode(substr($data['data'], (int) strpos($data['data'], 'base64,') + 7), strict: true),
		);
	}

	public static function fromJson(string $json): self
	{
		/** @var array{brokerId: int, data: string} $data */
		$data = json_decode($json, assoc: true);
		return self::fromArray($data);
	}
}
