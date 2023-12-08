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
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[RefersTo(target: Group::class, nullable: true)]
		private ?Group $group,
	) {
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function setUser(User $user): void
	{
		$this->user = $user;
	}

	public function getTicker(): Ticker
	{
		return $this->ticker;
	}

	public function setTicker(Ticker $ticker): void
	{
		$this->ticker = $ticker;
	}

	public function getGroup(): ?Group
	{
		return $this->group;
	}

	public function setGroup(?Group $group): void
	{
		$this->group = $group;
	}
}
