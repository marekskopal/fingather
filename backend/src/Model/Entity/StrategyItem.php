<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Decimal\Decimal;
use FinGather\Model\Repository\StrategyItemRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: StrategyItemRepository::class)]
class StrategyItem extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: Strategy::class)]
		public readonly Strategy $strategy,
		#[ManyToOne(entityClass: Asset::class, nullable: true)]
		public readonly ?Asset $asset,
		#[ManyToOne(entityClass: Group::class, nullable: true)]
		public readonly ?Group $group,
		#[ColumnDecimal(precision: 5, scale: 2)]
		public Decimal $percentage,
	) {
	}
}
