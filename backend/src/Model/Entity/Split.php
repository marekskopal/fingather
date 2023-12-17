<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Repository\SplitRepository;

#[Entity(repository: SplitRepository::class)]
class Split extends AEntity
{
	public function __construct(
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $date,
		#[Column(type: 'decimal(8,4)')]
		private string $factor,
	) {
	}

	public function getTicker(): Ticker
	{
		return $this->ticker;
	}

	public function setTicker(Ticker $ticker): void
	{
		$this->ticker = $ticker;
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function setDate(DateTimeImmutable $date): void
	{
		$this->date = $date;
	}

	public function getFactor(): string
	{
		return $this->factor;
	}

	public function setFactor(string $factor): void
	{
		$this->factor = $factor;
	}
}
