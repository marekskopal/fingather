<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;

#[Entity(repository: AssetRepository::class)]
class Asset
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Ticker::class)]
		private Ticker $ticker,
		#[RefersTo(target: Group::class, nullable: true)]
		private ?Group $group,
	) {
	}

	public function getId(): int
	{
		return $this->id;
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
