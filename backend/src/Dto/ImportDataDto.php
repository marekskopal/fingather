<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Utils\Base64Utils;
use function Safe\json_decode;

readonly class ImportDataDto
{
	/** @param list<string> $data */
	public function __construct(public int $brokerId, public array $data)
	{
	}

	/** @param array{brokerId: int, data: list<string>} $data */
	public static function fromArray(array $data): self
	{
		return new self(
			brokerId: $data['brokerId'],
			data: Base64Utils::decodeList($data['data']),
		);
	}

	public static function fromJson(string $json): self
	{
		/** @var array{brokerId: int, data: list<string>} $data */
		$data = json_decode($json, assoc: true);
		return self::fromArray($data);
	}
}
