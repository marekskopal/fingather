<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;

#[Entity(repository: CurrencyRepository::class)]
final class Currency
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[Column(type: 'string')]
		public readonly int $code,
		#[Column(type: 'string')]
		public readonly string $name,
		#[Column(type: 'string')]
		public readonly string $symbol,
	) {
	}
}
