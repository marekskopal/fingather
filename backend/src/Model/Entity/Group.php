<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Repository\GroupRepository;
use Iterator;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Attribute\OneToMany;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: GroupRepository::class)]
class Group extends AEntity
{
	public const string OthersName = 'Others';
	public const string OthersColor = '#2c3d3f';

	/** @param Iterator<Asset> $assets */
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[Column(type: Type::String)]
		public string $name,
		#[Column(type: Type::String, size: 7)]
		public string $color,
		#[Column(type: Type::Boolean, default: false)]
		public bool $isOthers,
		#[OneToMany(entityClass: Asset::class)]
		public readonly Iterator $assets,
	) {
	}
}
