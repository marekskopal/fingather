<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Repository\UserRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: UserRepository::class)]
class User extends AEntity
{
	public function __construct(
		#[Column(type: Type::String)]
		public readonly string $email,
		#[Column(type: Type::String)]
		public string $password,
		#[Column(type: Type::String)]
		public string $name,
		#[ColumnEnum(enum: UserRoleEnum::class, default: UserRoleEnum::User)]
		public UserRoleEnum $role,
		#[Column(type: Type::Boolean, default: false)]
		public bool $isEmailVerified,
		#[Column(type: Type::Boolean, default: false)]
		public bool $isOnboardingCompleted,
	) {
	}
}
