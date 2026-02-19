<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Repository\GoalRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: GoalRepository::class)]
class Goal extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public Portfolio $portfolio,
		#[ColumnEnum(enum: GoalTypeEnum::class)]
		public GoalTypeEnum $type,
		#[ColumnDecimal(precision: 18, scale: 8)]
		public Decimal $targetValue,
		#[Column(type: Type::Timestamp, nullable: true, default: null)]
		public ?DateTimeImmutable $deadline,
		#[Column(type: Type::Boolean, default: true)]
		public bool $isActive,
		#[Column(type: Type::Timestamp, nullable: true, default: null)]
		public ?DateTimeImmutable $achievedAt,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $createdAt,
	) {
	}
}
