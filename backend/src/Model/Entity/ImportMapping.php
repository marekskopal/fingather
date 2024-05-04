<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\ImportMappingRepository;

#[Entity(repository: ImportMappingRepository::class)]
class ImportMapping extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[RefersTo(target: Broker::class)]
		private Broker $broker,
		#[Column(type: 'string')]
		private string $importTicker,
		#[RefersTo(target: Ticker::class)]
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
