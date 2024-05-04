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
		private string $code,
		#[Column(type: 'string(50)')]
		private string $name,
		#[Column(type: 'string(5)')]
		private string $symbol,
		#[RefersTo(target: self::class, nullable: true, innerKey:'multiply_currency_id')]
		private ?Currency $multiplyCurrency,
		#[Column(type: 'integer', default: 1)]
		private int $multiplier,
		#[Column(type: 'boolean', default: true)]
		private bool $isSelectable,
	) {
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getSymbol(): string
	{
		return $this->symbol;
	}

	public function getMultiplyCurrency(): ?Currency
	{
		return $this->multiplyCurrency;
	}

	public function getMultiplier(): int
	{
		return $this->multiplier;
	}

	public function getIsSelectable(): bool
	{
		return $this->isSelectable;
	}
}
