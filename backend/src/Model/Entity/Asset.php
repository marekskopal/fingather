<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\AssetRepository;

#[Entity(repository: AssetRepository::class)]
class Asset extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[RefersTo(target: Group::class)]
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
