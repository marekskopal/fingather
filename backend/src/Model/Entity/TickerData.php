<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Repository\TickerDataRepository;

#[Entity(repository: TickerDataRepository::class)]
class TickerData extends AEntity
{
	public function __construct(
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $date,
		#[Column(type: 'decimal(20,10)')]
		private float $open,
		#[Column(type: 'decimal(20,10)')]
		private float $close,
		#[Column(type: 'decimal(20,10)')]
		private float $high,
		#[Column(type: 'decimal(20,10)')]
		private float $low,
		#[Column(type: 'integer')]
		private int $volume,
		#[Column(type: 'double')]
		private float $performance,
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

	public function getOpen(): float
	{
		return $this->open;
	}

	public function setOpen(float $open): void
	{
		$this->open = $open;
	}

	public function getClose(): float
	{
		return $this->close;
	}

	public function setClose(float $close): void
	{
		$this->close = $close;
	}

	public function getHigh(): float
	{
		return $this->high;
	}

	public function setHigh(float $high): void
	{
		$this->high = $high;
	}

	public function getLow(): float
	{
		return $this->low;
	}

	public function setLow(float $low): void
	{
		$this->low = $low;
	}

	public function getVolume(): int
	{
		return $this->volume;
	}

	public function setVolume(int $volume): void
	{
		$this->volume = $volume;
	}

	public function getPerformance(): float
	{
		return $this->performance;
	}

	public function setPerformance(float $performance): void
	{
		$this->performance = $performance;
	}
}
