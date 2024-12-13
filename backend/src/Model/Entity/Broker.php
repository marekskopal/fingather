<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Repository\BrokerRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: BrokerRepository::class)]
class Broker extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[Column(type: 'string')]
		public string $name,
		#[ColumnEnum(enum: BrokerImportTypeEnum::class)]
		public BrokerImportTypeEnum $importType,
	) {
	}
}
