<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\McpApiKey;

final readonly class McpApiKeyDto
{
	public function __construct(public int $id, public string $name, public string $apiKey, public string $createdAt)
	{
	}

	public static function fromEntity(McpApiKey $entity): self
	{
		return new self(
			id: $entity->id,
			name: $entity->name,
			apiKey: self::mask($entity->apiKey),
			createdAt: $entity->createdAt->format('Y-m-d H:i:s'),
		);
	}

	private static function mask(string $value): string
	{
		$visibleLength = min(4, strlen($value));
		return str_repeat('*', max(0, strlen($value) - $visibleLength)) . substr($value, -$visibleLength);
	}
}
