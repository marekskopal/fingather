<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Repository\UserRepository;
use MarekSkopal\Cycle\Enum\ColumnEnum;

#[Entity(repository: UserRepository::class)]
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
