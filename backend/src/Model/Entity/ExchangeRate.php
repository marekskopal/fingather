<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\ExchangeRateRepository;
use FinGather\Service\Dbal\DecimalTypecast;

#[Entity(repository: ExchangeRateRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class ExchangeRate extends AEntity
{
	public function __construct(
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $date,
		#[Column(type: 'decimal(9,4)', typecast: DecimalTypecast::Type)]
		private Decimal $rate,
	) {
	}

	public function getCurrency(): Currency
	{
		return $this->currency;
	}

	public function setCurrency(Currency $currency): void
	{
		$this->currency = $currency;
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function setDate(DateTimeImmutable $date): void
	{
		$this->date = $date;
	}

	public function getRate(): Decimal
	{
		return $this->rate;
	}

	public function setRate(Decimal $rate): void
	{
		$this->rate = $rate;
	}
}
