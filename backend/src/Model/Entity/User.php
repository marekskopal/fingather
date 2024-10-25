<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Attribute\ColumnEnum;
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
		#[ColumnEnum(enum: UserRoleEnum::class)]
		private UserRoleEnum $role,
		#[Column(type: 'boolean', default: false)]
		private bool $isEmailVerified,
		#[Column(type: 'boolean', default: false)]
		private bool $isOnboardingCompleted,
	) {
	}

	public function getEmail(): string
	{
		return $this->email;
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

	public function isOnboardingCompleted(): bool
	{
		return $this->isOnboardingCompleted;
	}

	public function setIsOnboardingCompleted(bool $isOnboardingCompleted): void
	{
		$this->isOnboardingCompleted = $isOnboardingCompleted;
	}
}
