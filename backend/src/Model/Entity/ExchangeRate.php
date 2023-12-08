<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\ActionTypeEnum;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\ExchangeRateRepository;

#[Entity(repository: ExchangeRateRepository::class)]
class ExchangeRate extends AEntity
{
	public function __construct(
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'timestamp')]
		private \DateTime $date,
		#[Column(type: 'decimal(10,10)')]
		private float $rate,
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

	public function getDate(): \DateTime
	{
		return $this->date;
	}

	public function setDate(\DateTime $date): void
	{
		$this->date = $date;
	}

	public function getRate(): float
	{
		return $this->rate;
	}

	public function setRate(float $rate): void
	{
		$this->rate = $rate;
	}
}
