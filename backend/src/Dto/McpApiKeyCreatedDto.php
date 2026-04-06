<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\McpApiKey;

/** Returned only at key creation — includes the full raw key (shown once). */
final readonly class McpApiKeyCreatedDto
{
	public function __construct(
		public int $id,
		public string $name,
		public string $keyPrefix,
		public string $createdAt,
		public string $key,
	) {
	}

	public static function fromEntity(McpApiKey $entity, string $rawKey): self
	{
		return new self(
			id: $entity->id,
			name: $entity->name,
			keyPrefix: $entity->keyPrefix,
			createdAt: $entity->createdAt->format('Y-m-d H:i:s'),
			key: $rawKey,
		);
	}
}
