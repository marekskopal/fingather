<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\GroupRepository;

#[Entity(repository: GroupRepository::class)]
final class Group
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[HasOne(target: User::class)]
		public readonly Currency $defaultCurrency,
		#[Column(type: 'string')]
		public readonly string $name,
	) {
	}
}
