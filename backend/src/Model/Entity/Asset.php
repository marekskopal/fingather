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
		public readonly User $user,
		#[RefersTo(target: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[RefersTo(target: Ticker::class)]
		public readonly Ticker $ticker,
		#[RefersTo(target: Group::class)]
		public Group $group,
	) {
	}
}
