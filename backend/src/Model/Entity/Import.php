<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Entity\Behavior\Uuid\Uuid4;
use DateTimeImmutable;
use FinGather\Model\Repository\ImportRepository;
use Ramsey\Uuid\UuidInterface;

#[Entity(repository: ImportRepository::class)]
#[Uuid4(nullable: false)]
class Import extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $created,
		#[Column(type: 'uuid', field: 'uuid')]
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
