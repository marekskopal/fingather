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
		private User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		private Portfolio $portfolio,
		#[ManyToOne(entityClass: Ticker::class)]
		private Ticker $ticker,
		#[ManyToOne(entityClass: Group::class)]
		private Group $group,
	) {
	}

	public function getPortfolio(): Portfolio
	{
		return $this->portfolio;
	}

	public function getTicker(): Ticker
	{
		return $this->ticker;
	}

	public function getGroup(): Group
	{
		return $this->group;
	}

	public function setGroup(Group $group): void
	{
		$this->group = $group;
	}
}
