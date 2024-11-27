<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\ApiImport;

/**
 * @implements ArrayFactoryInterface<array{
 *     apiImportId: int,
 * }>
 */
final readonly class ApiImportProcessCheckDto implements ArrayFactoryInterface
{
	public function __construct(public int $apiImportId)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(apiImportId: $data['apiImportId']);
	}

	public static function fromEntity(ApiImport $apiImport): self
	{
		return new self(
			apiImportId: $apiImport->id,
		);
	}
}
