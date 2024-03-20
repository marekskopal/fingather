<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\TickerDataRepository;
use MarekSkopal\Cycle\Decimal\ColumnDecimal;
use MarekSkopal\Cycle\Decimal\DecimalTypecast;

#[Entity(repository: TickerDataRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class TickerData extends AEntity
{
	public function __construct(
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $date,
		#[ColumnDecimal(precision: 20, scale: 10)]
		private Decimal $open,
		#[ColumnDecimal(precision: 20, scale: 10)]
		private Decimal $close,
		#[ColumnDecimal(precision: 20, scale: 10)]
		private Decimal $high,
		#[ColumnDecimal(precision: 20, scale: 10)]
		private Decimal $low,
		#[ColumnDecimal(precision: 20, scale: 10)]
		private Decimal $volume,
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

	public function getOpen(): Decimal
	{
		return $this->open;
	}

	public function setOpen(Decimal $open): void
	{
		$this->open = $open;
	}

	public function getClose(): Decimal
	{
		return $this->close;
	}

	public function setClose(Decimal $close): void
	{
		$this->close = $close;
	}

	public function getHigh(): Decimal
	{
		return $this->high;
	}

	public function setHigh(Decimal $high): void
	{
		$this->high = $high;
	}

	public function getLow(): Decimal
	{
		return $this->low;
	}

	public function setLow(Decimal $low): void
	{
		$this->low = $low;
	}

	public function getVolume(): Decimal
	{
		return $this->volume;
	}

	public function setVolume(Decimal $volume): void
	{
		$this->volume = $volume;
	}
}
