<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\AssetRepository;

#[Entity(repository: AssetRepository::class)]
class Asset extends AEntity
{
	/** @param array<Transaction> $transactions */
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[RefersTo(target: Group::class)]
		private Group $group,
		#[HasMany(target: Transaction::class)]
		private array $transactions,
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

	public function getPortfolio(): Portfolio
	{
		return $this->portfolio;
	}

	public function setPortfolio(Portfolio $portfolio): void
	{
		$this->portfolio = $portfolio;
	}

	public function getTicker(): Ticker
	{
		return $this->ticker;
	}

	public function setTicker(Ticker $ticker): void
	{
		$this->ticker = $ticker;
	}

	public function getGroup(): Group
	{
		return $this->group;
	}

	public function setGroup(Group $group): void
	{
		$this->group = $group;
	}

	/** @return array<Transaction> */
	public function getTransactions(): array
	{
		return $this->transactions;
	}
}
