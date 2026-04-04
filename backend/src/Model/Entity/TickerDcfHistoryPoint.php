<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\TickerDcfHistoryPointRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: TickerDcfHistoryPointRepository::class)]
class TickerDcfHistoryPoint extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Ticker::class)]
		public readonly Ticker $ticker,
		#[Column(type: Type::Date)]
		public DateTimeImmutable $fiscalDate,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $freeCashFlow,
		#[Column(type: Type::BigInt, nullable: true)]
		public ?int $revenue,
	) {
	}
}
