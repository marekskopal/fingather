<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\ForeignKey;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\SplitRepository;
use MarekSkopal\Cycle\Decimal\ColumnDecimal;
use MarekSkopal\Cycle\Decimal\DecimalTypecast;

#[Entity(repository: SplitRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class Split extends AEntity
{
	public function __construct(
		#[Column(type: 'integer')]
		#[ForeignKey(target: Ticker::class)]
		private int $tickerId,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $date,
		#[ColumnDecimal(precision: 8, scale: 4)]
		private Decimal $factor,
	) {
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function getFactor(): Decimal
	{
		return $this->factor;
	}
}
