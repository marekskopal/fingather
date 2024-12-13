<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\ImportRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use Ramsey\Uuid\UuidInterface;

#[Entity(repositoryClass: ImportRepository::class)]
class Import extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[Column(type: 'timestamp')]
		public readonly DateTimeImmutable $created,
		#[Column(type: 'uuid')]
		public readonly UuidInterface $uuid,
	) {
	}
}
