<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\TickerDataRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;

#[Entity(repositoryClass: TickerDataRepository::class)]
class TickerData extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Ticker::class)]
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
		#[ColumnDecimal(precision: 22, scale: 10)]
		private Decimal $volume,
	) {
	}

	public function getTicker(): Ticker
	{
		return $this->ticker;
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function getOpen(): Decimal
	{
		return $this->open;
	}

	public function getClose(): Decimal
	{
		return $this->close;
	}

	public function getHigh(): Decimal
	{
		return $this->high;
	}

	public function getLow(): Decimal
	{
		return $this->low;
	}

	public function getVolume(): Decimal
	{
		return $this->volume;
	}
}
