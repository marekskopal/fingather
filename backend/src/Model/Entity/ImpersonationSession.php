<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\ImpersonationTerminationReasonEnum;
use FinGather\Model\Repository\ImpersonationSessionRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: ImpersonationSessionRepository::class)]
class ImpersonationSession extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public User $adminUser,
		#[ManyToOne(entityClass: User::class)]
		public User $targetUser,
		#[Column(type: Type::Timestamp)]
		public DateTimeImmutable $startedAt,
		#[Column(type: Type::Timestamp, nullable: true, default: null)]
		public ?DateTimeImmutable $endedAt,
		#[Column(type: Type::String, size: 45, default: '')]
		public string $ipAddress,
		#[Column(type: Type::String, size: 255, default: '')]
		public string $userAgent,
		#[ColumnEnum(enum: ImpersonationTerminationReasonEnum::class, nullable: true, default: null)]
		public ?ImpersonationTerminationReasonEnum $terminationReason,
	) {
	}
}
