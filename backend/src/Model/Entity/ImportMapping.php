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
		private User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		private Portfolio $portfolio,
		#[ManyToOne(entityClass: Broker::class)]
		private Broker $broker,
		#[Column(type: 'string')]
		private string $importTicker,
		#[ManyToOne(entityClass: Ticker::class)]
		private Ticker $ticker,
	) {
	}

	public function getImportTicker(): string
	{
		return $this->importTicker;
	}

	public function getTicker(): Ticker
	{
		return $this->ticker;
	}
}
