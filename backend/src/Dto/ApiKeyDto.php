<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;

final readonly class ApiKeyDto
{
	public function __construct(public int $id, public ApiKeyTypeEnum $type, public string $apiKey)
	{
	}

	public static function fromEntity(ApiKey $entity): self
	{
		return new self(
			id: $entity->id,
			type: $entity->type,
			apiKey: $entity->apiKey,
		);
	}
}
