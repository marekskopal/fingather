<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\SplitRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ForeignKey;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: SplitRepository::class)]
class Split extends AEntity
{
	public function __construct(
		#[Column(type: Type::Int)]
		#[ForeignKey(entityClass: Ticker::class)]
		public readonly int $tickerId,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $date,
		#[ColumnDecimal(precision: 8, scale: 4)]
		public readonly Decimal $factor,
	) {
	}
}
