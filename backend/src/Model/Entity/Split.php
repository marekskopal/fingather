<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\SplitRepository;

#[Entity(repository: SplitRepository::class)]
final class Split
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[HasOne(target: Ticker::class)]
		public readonly Ticker $ticker,
		#[Column(type: 'timestamp')]
		public readonly \DateTime $date,
		#[Column(type: 'decimal(10,10)')]
		public readonly float $factor,
	) {
	}
}
