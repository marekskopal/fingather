<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Service\Dbal\DecimalTypecast;

#[Entity(repository: SplitRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class Split extends AEntity
{
	public function __construct(
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $date,
		#[Column(type: 'decimal(8,4)', typecast: DecimalTypecast::Type)]
		private Decimal $factor,
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

	public function getFactor(): Decimal
	{
		return $this->factor;
	}

	public function setFactor(Decimal $factor): void
	{
		$this->factor = $factor;
	}
}
