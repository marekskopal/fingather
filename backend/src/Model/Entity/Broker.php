<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Repository\BrokerRepository;
use MarekSkopal\Cycle\Enum\ColumnEnum;

#[Entity(repository: BrokerRepository::class)]
class Broker extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		public readonly User $user,
		#[RefersTo(target: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[Column(type: 'string')]
		public string $name,
		#[ColumnEnum(enum: BrokerImportTypeEnum::class)]
		public BrokerImportTypeEnum $importType,
	) {
	}
}
