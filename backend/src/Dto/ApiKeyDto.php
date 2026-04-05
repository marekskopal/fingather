<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;

final readonly class ApiKeyDto
{
	public function __construct(public int $id, public ApiKeyTypeEnum $type, public string $apiKey, public ?string $userKey = null)
	{
	}

	public static function fromEntity(ApiKey $entity): self
	{
		return new self(
			id: $entity->id,
			type: $entity->type,
			apiKey: self::mask($entity->apiKey),
			userKey: $entity->userKey !== null ? self::mask($entity->userKey) : null,
		);
	}

	private static function mask(string $value): string
	{
		$visibleLength = min(4, strlen($value));
		return str_repeat('*', max(0, strlen($value) - $visibleLength)) . substr($value, -$visibleLength);
	}
}
