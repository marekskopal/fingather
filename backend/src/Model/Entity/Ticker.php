<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\TickerRepository;

#[Entity(repository: TickerRepository::class)]
final class Ticker
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[Column(type: 'string')]
		public readonly int $ticker,
		#[Column(type: 'string')]
		public readonly string $name,
		#[Column(type: 'string')]
		public readonly string $symbol,
		#[HasOne(target: Market::class)]
		public readonly Market $market,
		#[HasOne(target: Currency::class)]
		public readonly Currency $currency,
	) {
	}
}
