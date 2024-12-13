<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Repository\MarketRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: MarketRepository::class)]
class Market extends AEntity
{
	public function __construct(
		#[ColumnEnum(enum: MarketTypeEnum::class)]
		public readonly MarketTypeEnum $type,
		#[Column(type: 'string')]
		public readonly string $name,
		#[Column(type: 'string(20)')]
		public readonly string $acronym,
		#[Column(type: 'string(5)')]
		public readonly string $mic,
		#[Column(type: 'string(2)')]
		public readonly string $exchangeCode,
		#[Column(type: 'string(2)')]
		public readonly string $country,
		#[Column(type: 'string')]
		public readonly string $city,
		#[Column(type: 'string')]
		public readonly string $timezone,
		#[ManyToOne(entityClass: Currency::class)]
		public readonly Currency $currency,
	) {
	}
}
