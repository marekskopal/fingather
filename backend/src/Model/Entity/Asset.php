<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;

#[Entity(repository: AssetRepository::class)]
final class Asset
{
	#[Column(type: 'primary')]
	private int $id;

	public function __construct(
		#[HasOne(target: User::class)]
		public readonly User $user,
		#[HasOne(target: Ticker::class)]
		public readonly Ticker $ticker,
		#[HasOne(target: Group::class, nullable: true)]
		public readonly ?Group $group,
	) {
	}
}
