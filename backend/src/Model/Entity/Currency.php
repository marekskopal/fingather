<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\CurrencyRepository;

#[Entity(repository: CurrencyRepository::class)]
class Currency extends AEntity
{
	public function __construct(
		#[Column(type: 'string(3)',)]
		public readonly string $code,
		#[Column(type: 'string(50)')]
		public readonly string $name,
		#[Column(type: 'string(5)')]
		public readonly string $symbol,
		#[RefersTo(target: self::class, nullable: true, innerKey:'multiply_currency_id')]
		public readonly ?Currency $multiplyCurrency,
		#[Column(type: 'integer', default: 1)]
		public readonly int $multiplier,
		#[Column(type: 'boolean', default: true)]
		public readonly bool $isSelectable,
	) {
	}
}
