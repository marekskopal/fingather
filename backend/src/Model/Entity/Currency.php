<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\CurrencyRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: CurrencyRepository::class)]
class Currency extends AEntity
{
	public function __construct(
		#[Column(type: 'string(3)',)]
		public readonly string $code,
		#[Column(type: 'string(50)')]
		public readonly string $name,
		#[Column(type: 'string(5)')]
		public readonly string $symbol,
		#[ManyToOne(entityClass: self::class, nullable: true,)]
		public readonly ?Currency $multiplyCurrency,
		#[Column(type: 'integer', default: 1)]
		public readonly int $multiplier,
		#[Column(type: 'boolean', default: true)]
		public readonly bool $isSelectable,
	) {
	}
}
