<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\PortfolioRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: PortfolioRepository::class)]
class Portfolio extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Currency::class)]
		public Currency $currency,
		#[Column(type: Type::String)]
		public string $name,
		#[Column(type: Type::Boolean)]
		public bool $isDefault,
	) {
	}
}
