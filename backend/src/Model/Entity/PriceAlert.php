<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Repository\PriceAlertRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: PriceAlertRepository::class)]
class PriceAlert extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class, nullable: true)]
		public ?Portfolio $portfolio,
		#[ManyToOne(entityClass: Ticker::class, nullable: true)]
		public ?Ticker $ticker,
		#[ColumnEnum(enum: PriceAlertTypeEnum::class)]
		public PriceAlertTypeEnum $type,
		#[ColumnEnum(enum: AlertConditionEnum::class, name: 'condition_type')]
		public AlertConditionEnum $condition,
		#[ColumnDecimal(precision: 18, scale: 8)]
		public Decimal $targetValue,
		#[ColumnEnum(enum: AlertRecurrenceEnum::class)]
		public AlertRecurrenceEnum $recurrence,
		#[Column(type: Type::Int, default: 24)]
		public int $cooldownHours,
		#[Column(type: Type::Boolean, default: true)]
		public bool $isActive,
		#[Column(type: Type::Timestamp, nullable: true, default: null)]
		public ?DateTimeImmutable $lastTriggeredAt,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $createdAt,
	) {
	}
}
