<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\ImportMappingRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: ImportMappingRepository::class)]
class ImportMapping extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[ManyToOne(entityClass: Broker::class)]
		public readonly Broker $broker,
		#[Column(type: 'string')]
		public readonly string $importTicker,
		#[ManyToOne(entityClass: Ticker::class)]
		public readonly Ticker $ticker,
	) {
	}
}
