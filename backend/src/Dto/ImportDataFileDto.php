<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Utils\Base64Utils;
use JsonSerializable;

readonly class ImportDataFileDto implements JsonSerializable
{
	public function __construct(public string $fileName, public string $contents)
	{
	}

	/** @param array{fileName: string, contents: string} $data */
	public static function fromArray(array $data): self
	{
		return new self(
			fileName: $data['fileName'],
			contents: Base64Utils::decode($data['contents']),
		);
	}

	/** @return array{fileName: string, contents: string} */
	public function jsonSerialize(): array
	{
		return [
			'fileName' => $this->fileName,
			'contents' => Base64Utils::encode($this->contents),
		];
	}
}
