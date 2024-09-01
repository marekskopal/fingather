<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\ApiImport;

final readonly class ApiImportProcessCheckDto
{
	public function __construct(public int $apiImportId,)
	{
	}

	/**
	 * @param array{
	 *     apiImportId: int,
	 * } $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(apiImportId: $data['apiImportId']);
	}

	public static function fromEntity(ApiImport $apiImport): self
	{
		return new self(
			apiImportId: $apiImport->getId(),
		);
	}
}
