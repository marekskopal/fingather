<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;
use FinGather\Utils\DateTimeUtils;

final readonly class UserWithStatisticDto
{
	public function __construct(
		public int $id,
		public string $email,
		public string $name,
		public UserRoleEnum $role,
		public int $assetCount,
		public int $transactionCount,
		public ?string $lastLoggedIn,
		public ?string $lastRefreshTokenGenerated,
	) {
	}

	public static function fromEntity(User $entity, int $assetCount, int $transactionCount): self
	{
		return new self(
			id: $entity->id,
			email: $entity->email,
			name: $entity->name,
			role: $entity->role,
			assetCount: $assetCount,
			transactionCount: $transactionCount,
			lastLoggedIn: $entity->lastLoggedIn !== null ? DateTimeUtils::formatZulu($entity->lastLoggedIn) : null,
			lastRefreshTokenGenerated: $entity->lastRefreshTokenGenerated !== null ? DateTimeUtils::formatZulu(
				$entity->lastRefreshTokenGenerated,
			) : null,
		);
	}
}
