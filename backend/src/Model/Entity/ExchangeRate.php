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

#[Entity(repositoryClass: ExchangeRateRepository::class)]
class ExchangeRate extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Currency::class)]
		private Currency $currency,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $date,
		#[ColumnDecimal(precision: 9, scale: 4)]
		private Decimal $rate,
	) {
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function getRate(): Decimal
	{
		return $this->rate;
	}
}
