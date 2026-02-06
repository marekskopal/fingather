<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\BenchmarkAssetRepository;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: BenchmarkAssetRepository::class)]
class BenchmarkAsset extends AEntity
{
	public function __construct(#[ManyToOne(entityClass: Ticker::class)] public readonly Ticker $ticker,)
	{
	}
}
