<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\MarketRepository;

#[Entity(repository: MarketRepository::class)]
final class Market
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[Column(type: 'string')]
		public readonly int $name,
		#[Column(type: 'string')]
		public readonly string $acronym,
		#[Column(type: 'string')]
		public readonly string $mic,
		#[Column(type: 'string')]
		public readonly string $country,
		#[Column(type: 'string')]
		public readonly string $city,
		#[Column(type: 'string')]
		public readonly string $web,
		#[HasOne(target: Currency::class)]
		public readonly Currency $currency,
	) {
	}
}
