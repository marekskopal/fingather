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
		private string $code,
		#[Column(type: 'string(50)')]
		private string $name,
		#[Column(type: 'string(5)')]
		private string $symbol,
		#[ManyToOne(entityClass: self::class, nullable: true,)]
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
