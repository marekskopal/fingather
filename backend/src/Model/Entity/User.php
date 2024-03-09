<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Repository\UserRepository;

#[Entity(repository: UserRepository::class)]
class User extends AEntity
{
	public function __construct(
		#[Column(type: 'string')]
		private string $email,
		#[Column(type: 'string')]
		private string $password,
		#[Column(type: 'string')]
		private string $name,
		#[RefersTo(target: Currency::class, innerKey:'default_currency_id')]
		private Currency $defaultCurrency,
		#[Column(type: 'enum(User,Admin)', typecast: UserRoleEnum::class)]
		private UserRoleEnum $role,
		#[Column(type: 'boolean')]
		private bool $isEmailVerified,
	) {
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): void
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

	public function getRole(): UserRoleEnum
	{
		return $this->role;
	}

	public function setRole(UserRoleEnum $role): void
	{
		$this->role = $role;
	}

	public function isEmailVerified(): bool
	{
		return $this->isEmailVerified;
	}

	public function setIsEmailVerified(bool $isEmailVerified): void
	{
		$this->isEmailVerified = $isEmailVerified;
	}
}
