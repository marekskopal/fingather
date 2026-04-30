<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\ImpersonationTerminationReasonEnum;
use FinGather\Model\Entity\ImpersonationSession;
use FinGather\Utils\DateTimeUtils;

final readonly class ImpersonationSessionDto
{
	public function __construct(
		public int $id,
		public int $adminUserId,
		public string $adminUserEmail,
		public int $targetUserId,
		public string $targetUserEmail,
		public string $startedAt,
		public ?string $endedAt,
		public string $ipAddress,
		public string $userAgent,
		public ?ImpersonationTerminationReasonEnum $terminationReason,
	) {
	}

	public static function fromEntity(ImpersonationSession $entity): self
	{
		return new self(
			id: $entity->id,
			adminUserId: $entity->adminUser->id,
			adminUserEmail: $entity->adminUser->email,
			targetUserId: $entity->targetUser->id,
			targetUserEmail: $entity->targetUser->email,
			startedAt: DateTimeUtils::formatZulu($entity->startedAt),
			endedAt: $entity->endedAt !== null ? DateTimeUtils::formatZulu($entity->endedAt) : null,
			ipAddress: $entity->ipAddress,
			userAgent: $entity->userAgent,
			terminationReason: $entity->terminationReason,
		);
	}
}
