<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\AssetRepository;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: AssetRepository::class)]
class Asset extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[ManyToOne(entityClass: Ticker::class)]
		public readonly Ticker $ticker,
		#[ManyToOne(entityClass: Group::class)]
		public Group $group,
	) {
	}
}
