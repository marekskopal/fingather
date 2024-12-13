<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Repository\UserRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;

#[Entity(repositoryClass: UserRepository::class)]
class User extends AEntity
{
	public function __construct(
		#[Column(type: 'string')]
		public readonly string $email,
		#[Column(type: 'string')]
		public string $password,
		#[Column(type: 'string')]
		public string $name,
		#[ColumnEnum(enum: UserRoleEnum::class)]
		public UserRoleEnum $role,
		#[Column(type: 'boolean', default: false)]
		public bool $isEmailVerified,
		#[Column(type: 'boolean', default: false)]
		public bool $isOnboardingCompleted,
	) {
	}
}
