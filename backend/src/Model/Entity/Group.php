<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\GroupRepository;

#[Entity(repository: GroupRepository::class)]
class Group extends AEntity
{
	public const string OthersName = 'Others';
	public const string OthersColor = '#2c3d3f';

	/** @param array<int, Asset> $assets */
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[Column(type: 'string')]
		private string $name,
		#[Column(type: 'string(7)')]
		private string $color,
		#[Column(type: 'boolean')]
		private bool $isOthers,
		#[HasMany(target: Asset::class)]
		private array $assets,
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

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getColor(): string
	{
		return $this->color;
	}

	public function setColor(string $color): void
	{
		$this->color = $color;
	}

	public function isOthers(): bool
	{
		return $this->isOthers;
	}

	public function setIsOthers(bool $isOthers): void
	{
		$this->isOthers = $isOthers;
	}

	/** @return array<int, Asset> */
	public function getAssets(): array
	{
		return $this->assets;
	}

	/** @param array<int, Asset> $assets */
	public function setAssets(array $assets): void
	{
		$this->assets = $assets;
	}
}
