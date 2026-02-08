<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\StrategyRepository;
use Iterator;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Attribute\OneToMany;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: StrategyRepository::class)]
class Strategy extends AEntity
{
	/** @param Iterator<StrategyItem> $strategyItems */
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[Column(type: Type::String)]
		public string $name,
		#[Column(type: Type::Boolean, default: false)]
		public bool $isDefault,
		#[OneToMany(entityClass: StrategyItem::class)]
		public readonly Iterator $strategyItems,
	) {
	}
}
