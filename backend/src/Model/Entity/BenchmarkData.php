<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Repository\BenchmarkDataRepository;

#[Entity(repository: BenchmarkDataRepository::class)]
class BenchmarkData extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		protected User $user,
		#[RefersTo(target: Asset::class)]
		protected Asset $asset,
		#[Column(type: 'timestamp')]
		protected DateTimeImmutable $date,
		#[Column(type: 'timestamp')]
		protected DateTimeImmutable $fromDate,
		#[Column(type: 'decimal(11,2)')]
		protected string $value,
		#[Column(type: 'decimal(18,8)')]
		protected string $units,
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

	public function getAsset(): Asset
	{
		return $this->asset;
	}

	public function setAsset(Asset $asset): void
	{
		$this->asset = $asset;
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function setDate(DateTimeImmutable $date): void
	{
		$this->date = $date;
	}

	public function getFromDate(): DateTimeImmutable
	{
		return $this->fromDate;
	}

	public function setFromDate(DateTimeImmutable $beforeDate): void
	{
		$this->fromDate = $beforeDate;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function setValue(string $value): void
	{
		$this->value = $value;
	}

	public function getUnits(): string
	{
		return $this->units;
	}

	public function setUnits(string $units): void
	{
		$this->units = $units;
	}
}
