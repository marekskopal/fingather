<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\UserRepository;

#[Entity(repository: UserRepository::class)]
class User
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[Column(type: 'string')]
		private int $email,
		#[Column(type: 'string')]
		private string $password,
		#[Column(type: 'string')]
		private string $name,
		#[RefersTo(target: Currency::class, innerKey:'default_currency_id')]
		private Currency $defaultCurrency,
	) {
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getEmail(): int
	{
		return $this->email;
	}

	public function setEmail(int $email): void
	{
		$this->email = $email;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	public function setPassword(string $password): void
	{
		$this->password = $password;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getDefaultCurrency(): Currency
	{
		return $this->defaultCurrency;
	}

	public function setDefaultCurrency(Currency $defaultCurrency): void
	{
		$this->defaultCurrency = $defaultCurrency;
	}
}
