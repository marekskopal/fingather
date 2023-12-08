<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\GroupRepository;

#[Entity(repository: GroupRepository::class)]
class Group
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[Column(type: 'string')]
		private string $name,
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

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}
}
