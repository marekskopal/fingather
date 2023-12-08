<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\SplitRepository;

#[Entity(repository: SplitRepository::class)]
class Split
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[Column(type: 'timestamp')]
		private \DateTime $date,
		#[Column(type: 'decimal(10,10)')]
		private float $factor,
	) {
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getTicker(): Ticker
	{
		return $this->ticker;
	}

	public function setTicker(Ticker $ticker): void
	{
		$this->ticker = $ticker;
	}

	public function getDate(): \DateTime
	{
		return $this->date;
	}

	public function setDate(\DateTime $date): void
	{
		$this->date = $date;
	}

	public function getFactor(): float
	{
		return $this->factor;
	}

	public function setFactor(float $factor): void
	{
		$this->factor = $factor;
	}
}
