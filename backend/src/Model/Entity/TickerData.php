<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\TickerDataRepository;
use FinGather\Model\Repository\TickerRepository;

#[Entity(repository: TickerDataRepository::class)]
final class TickerData
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[HasOne(target: Currency::class)]
		public readonly Ticker $ticker,
		#[Column(type: 'timestamp')]
		public readonly \DateTime $date,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $open,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $close,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $high,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $low,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $volume,
		#[Column(type: 'double')]
		public readonly float $performance,
	) {
	}
}
