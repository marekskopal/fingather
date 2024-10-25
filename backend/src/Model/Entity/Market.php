<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Attribute\ColumnEnum;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Repository\MarketRepository;

#[Entity(repository: MarketRepository::class)]
class Market extends AEntity
{
	public function __construct(
		#[ColumnEnum(enum: MarketTypeEnum::class)]
		private MarketTypeEnum $type,
		#[Column(type: 'string')]
		private string $name,
		#[Column(type: 'string(20)')]
		private string $acronym,
		#[Column(type: 'string(5)')]
		private string $mic,
		#[Column(type: 'string(2)')]
		private string $exchangeCode,
		#[Column(type: 'string(2)')]
		private string $country,
		#[Column(type: 'string')]
		private string $city,
		#[Column(type: 'string')]
		private string $timezone,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
	) {
	}

	public function getType(): MarketTypeEnum
	{
		return $this->type;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getAcronym(): string
	{
		return $this->acronym;
	}

	public function getMic(): string
	{
		return $this->mic;
	}

	public function getExchangeCode(): string
	{
		return $this->exchangeCode;
	}

	public function getCountry(): string
	{
		return $this->country;
	}

	public function getCity(): string
	{
		return $this->city;
	}

	public function getTimezone(): string
	{
		return $this->timezone;
	}

	public function getCurrency(): Currency
	{
		return $this->currency;
	}
}
