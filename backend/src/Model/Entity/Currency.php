<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\CurrencyRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: CurrencyRepository::class)]
class Currency extends AEntity
{
	public function __construct(
		#[Column(type: Type::String, size: 3)]
		public readonly string $code,
		#[Column(type: Type::String, size: 50)]
		public readonly string $name,
		#[Column(type: Type::String, size: 5)]
		public readonly string $symbol,
		#[ManyToOne(entityClass: self::class, nullable: true,)]
		public readonly ?Currency $multiplyCurrency,
		#[Column(type: Type::Int, default: 1)]
		public readonly int $multiplier,
		#[Column(type: Type::Boolean, default: true)]
		public readonly bool $isSelectable,
	) {
	}
}
