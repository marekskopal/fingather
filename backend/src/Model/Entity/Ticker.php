<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\TickerRepository;

#[Entity(repository: TickerRepository::class)]
class Ticker extends AEntity
{
	public function __construct(
		#[Column(type: 'string(20)')]
		private int $ticker,
		#[Column(type: 'string')]
		private string $name,
		#[RefersTo(target: Market::class)]
		private Market $market,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
	) {
	}

	public function getTicker(): int
	{
		return $this->ticker;
	}

	public function setTicker(int $ticker): void
	{
		$this->ticker = $ticker;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getMarket(): Market
	{
		return $this->market;
	}

	public function setMarket(Market $market): void
	{
		$this->market = $market;
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
