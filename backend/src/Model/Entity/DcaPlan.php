<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Model\Repository\DcaPlanRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: DcaPlanRepository::class)]
class DcaPlan extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ColumnEnum(enum: DcaPlanTargetTypeEnum::class)]
		public DcaPlanTargetTypeEnum $targetType,
		#[ManyToOne(entityClass: Portfolio::class)]
		public Portfolio $portfolio,
		#[ManyToOne(entityClass: Asset::class, nullable: true)]
		public ?Asset $asset,
		#[ManyToOne(entityClass: Group::class, nullable: true)]
		public ?Group $group,
		#[ManyToOne(entityClass: Strategy::class, nullable: true)]
		public ?Strategy $strategy,
		#[ColumnDecimal(precision: 18, scale: 8)]
		public Decimal $amount,
		#[ManyToOne(entityClass: Currency::class)]
		public Currency $currency,
		#[Column(type: Type::Int, default: 1)]
		public int $intervalMonths,
		#[Column(type: Type::Timestamp)]
		public DateTimeImmutable $startDate,
		#[Column(type: Type::Timestamp, nullable: true, default: null)]
		public ?DateTimeImmutable $endDate,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $createdAt,
	) {
	}
}
