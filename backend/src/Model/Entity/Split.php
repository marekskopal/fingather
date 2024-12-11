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

#[Entity(repositoryClass: SplitRepository::class)]
class Split extends AEntity
{
	public function __construct(
		#[Column(type: 'integer')]
		#[ForeignKey(entityClass: Ticker::class)]
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
