<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Repository\ImportRepository;

#[Entity(repository: ImportRepository::class)]
class Import extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[RefersTo(target: Broker::class)]
		private Broker $broker,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $created,
		#[Column(type: 'longText')]
		private string $csvContent,
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

	public function getBroker(): Broker
	{
		return $this->broker;
	}

	public function setBroker(Broker $broker): void
	{
		$this->broker = $broker;
	}

	public function getCreated(): DateTimeImmutable
	{
		return $this->created;
	}

	public function setCreated(DateTimeImmutable $created): void
	{
		$this->created = $created;
	}

	public function getCsvContent(): string
	{
		return $this->csvContent;
	}

	public function setCsvContent(string $csvContent): void
	{
		$this->csvContent = $csvContent;
	}
}