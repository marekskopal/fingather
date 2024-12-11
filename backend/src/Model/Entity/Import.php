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
		private User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		private Portfolio $portfolio,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $created,
		#[Column(type: 'uuid')]
		private UuidInterface $uuid,
	) {
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function getPortfolio(): Portfolio
	{
		return $this->portfolio;
	}

	public function getUuid(): UuidInterface
	{
		return $this->uuid;
	}
}
