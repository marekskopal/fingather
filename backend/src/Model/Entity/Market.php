<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Repository\MarketRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: MarketRepository::class)]
class Market extends AEntity
{
	public function __construct(
		#[ColumnEnum(enum: MarketTypeEnum::class, default: MarketTypeEnum::Stock)]
		public readonly MarketTypeEnum $type,
		#[Column(type: Type::String)]
		public readonly string $name,
		#[Column(type: Type::String, size: 20)]
		public readonly string $acronym,
		#[Column(type: Type::String, size: 5)]
		public readonly string $mic,
		#[Column(type: Type::String, size: 2)]
		public readonly string $exchangeCode,
		#[Column(type: Type::String, size: 2)]
		public readonly string $country,
		#[Column(type: Type::String)]
		public readonly string $city,
		#[Column(type: Type::String)]
		public readonly string $timezone,
		#[ManyToOne(entityClass: Currency::class)]
		public readonly Currency $currency,
	) {
	}
}
