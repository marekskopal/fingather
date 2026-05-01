<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Repository\ProxyAssetRepository;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: ProxyAssetRepository::class)]
class ProxyAsset extends AEntity
{
	public function __construct(
		#[ColumnEnum(enum: TickerTypeEnum::class)]
		public readonly TickerTypeEnum $tickerType,
		#[ManyToOne(entityClass: Ticker::class)]
		public readonly Ticker $ticker,
	) {
	}
}
