<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\TickerDataRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: TickerDataRepository::class)]
class TickerData extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Ticker::class)]
		public readonly Ticker $ticker,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $date,
		#[ColumnDecimal(precision: 20, scale: 10)]
		public readonly Decimal $open,
		#[ColumnDecimal(precision: 20, scale: 10)]
		public readonly Decimal $close,
		#[ColumnDecimal(precision: 20, scale: 10)]
		public readonly Decimal $high,
		#[ColumnDecimal(precision: 20, scale: 10)]
		public readonly Decimal $low,
		#[ColumnDecimal(precision: 22, scale: 10)]
		public readonly Decimal $volume,
	) {
	}
}
