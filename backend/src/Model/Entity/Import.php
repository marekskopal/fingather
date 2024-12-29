<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\ImportRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;
use Ramsey\Uuid\UuidInterface;

#[Entity(repositoryClass: ImportRepository::class)]
class Import extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $created,
		#[Column(type: Type::Uuid)]
		public readonly UuidInterface $uuid,
	) {
	}
}
