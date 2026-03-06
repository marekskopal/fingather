<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\PasswordResetRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: PasswordResetRepository::class)]
class PasswordReset extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)] public readonly User $user,
		#[Column(type: Type::Uuid)] public readonly string $token,
		#[Column(type: Type::Timestamp)] public readonly DateTimeImmutable $createdAt,
	) {
	}
}
