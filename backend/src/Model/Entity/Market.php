<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\MarketRepository;

#[Entity(repository: MarketRepository::class)]
class Market extends AEntity
{
	public function __construct(
		#[Column(type: 'string')]
		private int $name,
		#[Column(type: 'string(20)')]
		private string $acronym,
		#[Column(type: 'string(4)')]
		private string $mic,
		#[Column(type: 'string(2)')]
		private string $country,
		#[Column(type: 'string')]
		private string $city,
		#[Column(type: 'string')]
		private string $web,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
	) {
	}

	public function getName(): int
	{
		return $this->name;
	}

	public function setName(int $name): void
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

	public function getWeb(): string
	{
		return $this->web;
	}

	public function setWeb(string $web): void
	{
		$this->web = $web;
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
