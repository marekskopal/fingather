<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Repository\MarketRepository;

#[Entity(repository: MarketRepository::class)]
class Market extends AEntity
{
	public function __construct(
		#[Column(type: 'enum(Stock,Crypto)', typecast: MarketTypeEnum::class)]
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

	public function setType(MarketTypeEnum $type): void
	{
		$this->type = $type;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getAcronym(): string
	{
		return $this->acronym;
	}

	public function setAcronym(string $acronym): void
	{
		$this->acronym = $acronym;
	}

	public function getMic(): string
	{
		return $this->mic;
	}

	public function setMic(string $mic): void
	{
		$this->mic = $mic;
	}

	public function getExchangeCode(): string
	{
		return $this->exchangeCode;
	}

	public function setExchangeCode(string $exchangeCode): void
	{
		$this->exchangeCode = $exchangeCode;
	}

	public function getCountry(): string
	{
		return $this->country;
	}

	public function setCountry(string $country): void
	{
		$this->country = $country;
	}

	public function getCity(): string
	{
		return $this->city;
	}

	public function setCity(string $city): void
	{
		$this->city = $city;
	}

	public function getTimezone(): string
	{
		return $this->timezone;
	}

	public function setTimezone(string $timezone): void
	{
		$this->timezone = $timezone;
	}

	public function getCurrency(): Currency
	{
		return $this->currency;
	}

	public function setCurrency(Currency $currency): void
	{
		$this->currency = $currency;
	}
}
