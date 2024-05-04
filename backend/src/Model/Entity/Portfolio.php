<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\PortfolioRepository;

#[Entity(repository: PortfolioRepository::class)]
class Portfolio extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'string')]
		private string $name,
		#[Column(type: 'boolean')]
		private bool $isDefault,
	) {
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function getCurrency(): Currency
	{
		return $this->currency;
	}

	public function setCurrency(Currency $currency): void
	{
		$this->currency = $currency;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getIsDefault(): bool
	{
		return $this->isDefault;
	}

	public function setIsDefault(bool $isDefault): void
	{
		$this->isDefault = $isDefault;
	}
}
