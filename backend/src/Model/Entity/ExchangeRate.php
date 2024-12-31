<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\ExchangeRateRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: ExchangeRateRepository::class)]
class ExchangeRate extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Currency::class)]
		public readonly Currency $currency,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $date,
		#[ColumnDecimal(precision: 9, scale: 4)]
		public readonly Decimal $rate,
	) {
	}
}
