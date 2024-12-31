<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Repository\TickerRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;

#[Entity(repositoryClass: TickerRepository::class)]
class Ticker extends AEntity
{
	public function __construct(
		#[Column(type: 'string', size: 20)]
		public readonly string $ticker,
		#[Column(type: 'string')]
		public readonly string $name,
		#[ManyToOne(entityClass: Market::class)]
		public readonly Market $market,
		#[ManyToOne(entityClass: Currency::class)]
		public readonly Currency $currency,
		#[ColumnEnum(enum: TickerTypeEnum::class, default: TickerTypeEnum::Stock->value)]
		public TickerTypeEnum $type,
		#[Column(type: 'string', nullable: true)]
		public ?string $isin,
		#[Column(type: 'string', nullable: true)]
		public ?string $logo,
		#[ManyToOne(entityClass: Sector::class)]
		public Sector $sector,
		#[ManyToOne(entityClass: Industry::class)]
		public Industry $industry,
		#[Column(type: 'string', nullable: true)]
		public ?string $website,
		#[Column(type: 'text', nullable: true)]
		public ?string $description,
		#[ManyToOne(entityClass: Country::class)]
		public Country $country,
	) {
	}
}
