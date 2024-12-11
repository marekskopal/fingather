<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\GroupRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Attribute\OneToMany;

#[Entity(repositoryClass: GroupRepository::class)]
class Group extends AEntity
{
	public const string OthersName = 'Others';
	public const string OthersColor = '#2c3d3f';

	/** @param \Iterator<Asset> $assets */
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		private User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		private Portfolio $portfolio,
		#[Column(type: 'string')]
		private string $name,
		#[Column(type: 'string(7)')]
		private string $color,
		#[Column(type: 'boolean')]
		private bool $isOthers,
		#[OneToMany(entityClass: Asset::class)]
		private \Iterator $assets,
	) {
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function getPortfolio(): Portfolio
	{
		return $this->portfolio;
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

	/** @return \Iterator<Asset> */
	public function getAssets(): \Iterator
	{
		return $this->assets;
	}
}
